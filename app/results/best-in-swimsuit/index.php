<?php
    const LOGIN_PAGE_PATH = '../../crud/';
    require_once '../../crud/auth.php';

    require_once '../../config/database.php';
    require_once '../../models/Event.php';
    require_once '../../models/Criterion.php';
    require_once '../../models/Judge.php';

    // involved criteria
    const CRITERIA = [
        [ 'id' => 4 ], // Beauty of the Face
        [ 'id' => 5 ]  // Figure
    ];

    // initialize titles
    $titles = ['Best in Swimsuit'];

    // initialize competition title, criteria, judges and teams (candidates)
    $competition_title = '';
    $criteria = [];
    $judges   = [];
    $teams    = [];
    for($i=0; $i<sizeof(CRITERIA); $i++) {
        $criterion = Criterion::findById(CRITERIA[$i]['id']);
        $criterion_key = 'criterion_' . $criterion->getId();
        $criteria[$criterion_key] = $criterion;
        $event = $criterion->getEvent();
        if($competition_title == '')
            $competition_title = $event->getCategory()->getCompetition()->getTitle();

        foreach($event->getAllJudges() as $judge) {
            $judge_key = 'judge_' . $judge->getId();
            if(!isset($judges[$judge_key]))
                $judges[$judge_key] = $judge;
        }
        foreach($event->getAllTeams() as $team) {
            $team_key = 'team_' . $team->getId();
            if(!isset($teams[$team_key]))
                $teams[$team_key] = $team;
        }
    }

    // tabulate and get results
    $results = [];
    foreach($teams as $team_key => $team) {
        $t = [
            'info'    => $team->toArray(),
            'inputs'  => [],
            'rating'  => [
                'total'   => 0,
                'average' => 0
            ],
            'rank'    => [
                'total'   => 0,
                'average'  => 0,
                'initial'  => [
                    'dense'      => 0,
                    'fractional' => 0
                ],
                'adjusted' => 0,
                'final'   => [
                    'dense'      => 0,
                    'fractional' => 0
                ]
            ],
            'title' => ''
        ];

        // get inputs
        foreach($judges as $judge_key => $judge) {
            $j = [
                'criteria' => [],
                'total'    => 0,
                'rank'     => [
                    'dense'      => 0,
                    'fractional' => 0
                ]
            ];
            foreach($criteria as $criterion_key => $criterion) {
                $value = $judge->getCriterionTeamRatingRow($criterion, $team)['value'];
                $j['criteria'][$criterion_key] = $value;
                $j['total'] += $value;
            }

            // accumulate team rating total
            $t['rating']['total'] += $j['total'];

            // append $j to $t['inputs']
            $t['inputs'][$judge_key] = $j;
        }

        // compute $t['rating']['average']
        if(sizeof($t['inputs']) > 0)
            $t['rating']['average'] = $t['rating']['total'] / sizeof($t['inputs']);

        // append $t to $results
        $results[$team_key] = $t;
    }

    // get judge rank and team total rank
    foreach($judges as $judge_key => $judge) {
        $unique_totals = [];
        foreach($teams as $team_key => $team) {
            $total = $results[$team_key]['inputs'][$judge_key]['total'];
            if(!in_array($total, $unique_totals))
                $unique_totals[] = $total;
        }

        // sort $unique_totals
        rsort($unique_totals);

        // get dense rank
        $rank_group = [];
        foreach($teams as $team_key => $team) {
            // get dense rank
            $dense_rank = 1 + array_search($results[$team_key]['inputs'][$judge_key]['total'], $unique_totals);
            $results[$team_key]['inputs'][$judge_key]['rank']['dense'] = $dense_rank;

            // push $team_key to $rank_group
            $rank_key = 'rank_' . $dense_rank;
            if(!isset($rank_group[$rank_key]))
                $rank_group[$rank_key] = [];
            $rank_group[$rank_key][] = $team_key;
        }

        // get fractional rank
        $ctr = 0;
        for($i = 0; $i < sizeof($unique_totals); $i++) {
            $key = 'rank_' . ($i + 1);
            $group = $rank_group[$key];
            $size = sizeof($group);
            $fractional_rank = $ctr + ((($size * ($size + 1)) / 2) / $size);

            // write $fractional_rank to $group members
            for($j = 0; $j < $size; $j++) {
                $results[$group[$j]]['inputs'][$judge_key]['rank']['fractional'] = $fractional_rank;

                // accumulate total rank
                $results[$group[$j]]['rank']['total'] += $fractional_rank;
            }

            $ctr += $size;
        }
    }

    // get average rank
    $unique_rank_averages = [];
    foreach($teams as $team_key => $team) {
        if(sizeof($results[$team_key]['inputs']) > 0)
            $results[$team_key]['rank']['average'] = $results[$team_key]['rank']['total'] / sizeof($results[$team_key]['inputs']);

        // push to $unique_rank_averages
        if(!in_array($results[$team_key]['rank']['average'], $unique_rank_averages))
            $unique_rank_averages[] = $results[$team_key]['rank']['average'];
    }

    // sort $unique_rank_averages
    sort($unique_rank_averages);

    // gather $rank_group (for getting initial fractional rank)
    $rank_group = [];
    foreach($teams as $team_key => $team) {
        // get dense rank
        $dense_rank = 1 + array_search($results[$team_key]['rank']['average'], $unique_rank_averages);
        $results[$team_key]['rank']['initial']['dense'] = $dense_rank;

        // push $team_key to $rank_group
        $rank_key = 'rank_' . $dense_rank;
        if(!isset($rank_group[$rank_key]))
            $rank_group[$rank_key] = [];
        $rank_group[$rank_key][] = $team_key;
    }

    // get initial fractional rank
    $unique_adjusted_ranks = [];
    $ctr = 0;
    for($i = 0; $i < sizeof($unique_rank_averages); $i++) {
        $key = 'rank_' . ($i + 1);
        $group = $rank_group[$key];
        $size = sizeof($group);
        $initial_fractional_rank = $ctr + ((($size * ($size + 1)) / 2) / $size);

        // write $initial_fractional_rank to $group members
        for($j = 0; $j < $size; $j++) {
            $results[$group[$j]]['rank']['initial']['fractional'] = $initial_fractional_rank;

            // compute adjusted rank
            $adjusted_rank = $initial_fractional_rank - ($results[$group[$j]]['rating']['average'] * 0.01);
            $results[$group[$j]]['rank']['adjusted'] = $adjusted_rank;

            // push to $unique_adjusted_ranks
            if(!in_array($adjusted_rank, $unique_adjusted_ranks))
                $unique_adjusted_ranks[] = $adjusted_rank;
        }

        $ctr += $size;
    }

    // sort $unique_adjusted_ranks
    sort($unique_adjusted_ranks);

    // gather $rank_group (for getting final fractional rank)
    $rank_group = [];
    foreach($teams as $team_key => $team) {
        // get dense rank
        $dense_rank = 1 + array_search($results[$team_key]['rank']['adjusted'], $unique_adjusted_ranks);
        $results[$team_key]['rank']['final']['dense'] = $dense_rank;

        // push $key to $rank_group
        $rank_key = 'rank_' . $dense_rank;
        if(!isset($rank_group[$rank_key]))
            $rank_group[$rank_key] = [];
        $rank_group[$rank_key][] = $team_key;
    }

    // get final fractional rank
    $unique_final_fractional_ranks = [];
    $ctr = 0;
    for($i = 0; $i < sizeof($unique_adjusted_ranks); $i++) {
        $key = 'rank_' . ($i + 1);
        $group = $rank_group[$key];
        $size = sizeof($group);
        $final_fractional_rank = $ctr + ((($size * ($size + 1)) / 2) / $size);

        // push to $unique_final_fractional_ranks
        if(!in_array($final_fractional_rank, $unique_final_fractional_ranks))
            $unique_final_fractional_ranks[] = $final_fractional_rank;

        // write $fractional_rank to $group members
        for($j = 0; $j < $size; $j++) {
            $results[$group[$j]]['rank']['final']['fractional'] = $final_fractional_rank;
        }

        $ctr += $size;
    }

    // sort $unique_final_fractional_ranks
    sort($unique_final_fractional_ranks);

    // determine winners
    $winners = [];
    $i = 0;
    foreach($titles as $title) {
        // update title of $unique_final_fractional_ranks[$i]'th team
        foreach($teams as $team_key => $team) {
            if($results[$team_key]['rank']['final']['fractional'] == $unique_final_fractional_ranks[$i]) {
                $results[$team_key]['title'] = $titles[$i];
                $winners[] = $team->getId();
            }
        }

        $i += 1;
        if($i >= sizeof($unique_final_fractional_ranks))
            break;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../crud/dist/bootstrap-5.2.3/css/bootstrap.min.css">
    <style>
        th, td {
            vertical-align: middle;
        },
        .bt {
            border-top: 2px solid #aaa !important;
        }
        .br {
            border-right: 2px solid #aaa !important;
        }
        .bb, table.result > tbody tr:last-child td {
            border-bottom: 2px solid #aaa !important;
        }
        .bl {
            border-left: 2px solid #aaa !important;
        }
    </style>
    <title>Best in Swimsuit | <?= $competition_title ?></title>
</head>
<body>
    <div class="p-1">
        <table class="table table-bordered result">
            <thead class="bt">
                <tr class="table-secondary">
                    <th colspan="3" rowspan="2" class="text-center bt br bl bb">
                        <h2 class="m-0">Best in Swimsuit</h2>
                        <h5 class="text-uppercase"><?= $competition_title ?></h5>
                    </th>
                    <?php foreach($judges as $judge_key => $judge) { ?>
                        <th colspan="2" class="text-center text-success bt br">
                            <h5 class="m-0">JUDGE <?= $judge->getNumber() ?></h5>
                        </th>
                    <?php } ?>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0 opacity-75">TOTAL<br>AVG.</h5>
                    </th>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0 text-primary">RANK<br>TOTAL</h5>
                    </th>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0 text-primary">RANK<br>AVG.</h5>
                    </th>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0 text-secondary">INITIAL<br>RANK</h5>
                    </th>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0">FINAL<br>RANK</h5>
                    </th>
                    <th rowspan="2" class="text-center bb bt br">
                        <h5 class="m-0">TITLE</h5>
                    </th>
                </tr>
                <tr class="table-secondary">
                    <?php foreach($judges as $judge_key => $judge) { ?>
                        <th class="text-center bb">
                            <?php for($i = 0; $i < sizeof(CRITERIA); $i++) { ?>
                                <div class="fw-normal opacity-75"><small>C<?= $i + 1 ?></small></div>
                            <?php } ?>
                        </th>
                        <th class="text-center bb br">
                            <div class="text-secondary">TOTAL</div>
                            <div class="text-primary">RANK</div>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach($results as $team_key => $team) { ?>
                <tr<?= $team['title'] !== '' ? ' class="table-warning"' : '' ?>>
                    <!-- number -->
                    <td class="pe-3 fw-bold bl bb" align="right">
                        <h3 class="m-0">
                            <?= $team['info']['number'] ?>
                        </h3>
                    </td>

                    <!-- avatar -->
                    <td class="bb" style="width: 64px;">
                        <img
                            src="../../crud/uploads/<?= $team['info']['avatar'] ?>"
                            alt="<?= $team['info']['number'] ?>"
                            style="width: 64px; border-radius: 100%"
                        >
                    </td>

                    <!-- name -->
                    <td class="br bb">
                        <h6 class="text-uppercase m-0"><?= $team['info']['name'] ?></h6>
                        <small class="m-0"><?= $team['info']['location'] ?></small>
                    </td>

                    <!-- inputs -->
                    <?php foreach($team['inputs'] as $judge_key => $input) { ?>
                        <!-- criteria ratings -->
                        <td class="bb" align="right">
                            <?php foreach($input['criteria'] as $criterion_key => $value) { ?>
                                <div class="opacity-75 pe-2">
                                    <small><?= number_format($value, 2) ?></small>
                                </div>
                            <?php } ?>
                        </td>

                        <!-- total and rank -->
                        <td class="bb br" align="right">
                            <div class="pe-2">
                                <?= number_format($input['total'], 2) ?>
                            </div>
                            <div class="pe-2 text-primary fw-bold opacity-75">
                                <?= number_format($input['rank']['fractional'], 2) ?>
                            </div>
                        </td>
                    <?php } ?>

                    <!-- total average -->
                    <td class="bb br" align="right">
                        <div class="pe-2 text-secondary fw-bold">
                            <?= number_format($team['rating']['average'], 2) ?>
                        </div>
                    </td>

                    <!-- rank total -->
                    <td class="bb br" align="right">
                        <div class="pe-2 text-primary fw-bold">
                            <?= number_format($team['rank']['total'], 2) ?>
                        </div>
                    </td>

                    <!-- rank average -->
                    <td class="bb br" align="right">
                        <div class="pe-2 text-primary fw-bold">
                            <?= number_format($team['rank']['average'], 2) ?>
                        </div>
                    </td>

                    <!-- initial rank -->
                    <td class="bb br" align="right">
                        <h5 class="m-0 pe-1 text-secondary">
                            <?= number_format($team['rank']['initial']['fractional'], 2) ?>
                        </h5>
                    </td>

                    <!-- final rank -->
                    <td class="bb br" align="right">
                        <h5 class="m-0 pe-1">
                            <?= number_format($team['rank']['final']['fractional'], 2) ?>
                        </h5>
                    </td>

                    <!-- title -->
                    <td class="bb br text-center">
                        <h6 class="m-0 fw-bold">
                            <?= $team['title'] ?>
                        </h6>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
                <tr class="text-secondary">
                    <td colspan="<?= 9 + (sizeof($judges) * 2) ?>" class="bl bb br pt-3 px-3" style="vertical-align: middle !important;" align="center">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex">
                                <table class="me-4">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="text-center">CRITERIA</th>
                                            <th class="text-center">PORTION</th>
                                            <th class="text-center">POINTS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 0;
                                    $total_criteria_points = 0;
                                    foreach($criteria as $criterion_key => $criterion) {
                                        $i += 1;
                                        $total_criteria_points += $criterion->getPercentage();
                                    ?>
                                        <tr>
                                            <td class="px-3">
                                                <h6 class="m-0 fw-bold">C<?= $i ?></h6>
                                            </td>
                                            <td class="px-3"><?= $criterion->getTitle() ?></td>
                                            <td class="px-3"><?= $criterion->getEvent()->getTitle() ?></td>
                                            <td align="right" class="px-3"><?= number_format($criterion->getPercentage(), 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="px-3 pt-2" style="text-align: right !important;">TOTAL : </th>
                                            <th class="px-3 pt-2" style="text-align: right !important;"><?= number_format($total_criteria_points, 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <img src="/mk/aclc-iriga.png" class="ml-auto" style="width: 200px; opacity: 0.7" alt="Official Tabulator">
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Judges -->
        <div class="container-fluid mt-4 pb-2">
            <div class="row justify-content-center">
                <?php foreach($judges as $judge) { ?>
                    <div class="col-md-3">
                        <div class="mt-5 pt-3 text-center">
                            <h6 class="mb-0"><?= $judge->getName() ?></h6>
                        </div>
                        <div class="text-center">
                            <p class="mb-0">
                                JUDGE <?= $judge->getNumber() ?>
                                <?php if($judge->isChairmanOfEvent((array_values($criteria)[0])->getEvent())) { ?>
                                    * (Chairman)
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row justify-content-center pt-5 mt-5" style="page-break-before: always">
        <div class="col-12 col-sm-8 col-md-7 col-lg-6">
            <table class="table">
                <thead>
                <tr>
                    <th colspan="3" class="text-center pb-5">
                        <h3 class="text-uppercase"><?= $competition_title ?></h3>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total_winners = sizeof($winners);
                for($i = ($total_winners - 1); $i >= 0; $i--) {
                    $team = $results['team_'.$winners[$i]];
                    ?>
                    <?php if($i < ($total_winners - 1)) { ?>
                        <tr>
                            <td colspan="3" style="height: 100px;"></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" class="pa-3 text-center" style="border: 1px solid #ddd">
                            <h3 class="m-0 fw-bold"><?= $team['title'] ?></h3>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="text-center font-weight-bold pl-3 py-3 pr-6"
                            style="border-left: 1px solid #ddd; border-bottom: 1px solid #ddd;"
                        >
                            <h2 class="m-0 fw-bold"><?= $team['info']['number'] ?></h2>
                        </td>
                        <td style="width: 88px; padding-top: 8px !important; padding-bottom: 8px !important; border-bottom: 1px solid #ddd;">
                            <img
                                style="width: 100%; border-radius: 100%;"
                                src="../../crud/uploads/<?= $team['info']['avatar'] ?>"
                            />
                        </td>
                        <td
                            class="pa-3"
                            style="border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;"
                        >
                            <h5 class="m-0 text-uppercase fw-bold" style="line-height: 1.2"><?= $team['info']['name'] ?></h5>
                            <p class="mt-1 text-body-1 mb-0" style="line-height: 1"><small><?= $team['info']['location'] ?></small></p>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
