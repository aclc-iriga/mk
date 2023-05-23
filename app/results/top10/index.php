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
        [ 'id' => 5 ], // Figure
        [ 'id' => 6 ]  // Personality / Intelligence
    ];

    // initialize titles
    $titles = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    // initialize location quota
    const LOCATION_QUOTA = 2;

    // initialize color code
    const COLOR_CODE = [
        'legitimate' => 'primary',
        'inserted'   => 'success',
        'outplaced'  => 'danger'
    ];

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
            'title' => [
                'slot'   => '',
                'status' => ''
            ]
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
                $value = 0;
                if($judge->hasEvent($criterion->getEvent()))
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

    // determine overall ranking for getting winners
    $overall = [];
    $overall_ctr  = 0;
    $title_ctr    = 0;
    $location_ctr = 0;
    for($i = 0; $i < sizeof($unique_final_fractional_ranks); $i++) {
        $group = [];
        foreach($teams as $team_key => $team) {
            if($results[$team_key]['rank']['final']['fractional'] == $unique_final_fractional_ranks[$i]) {
                $group[] = [
                    'team_key' => $team_key,
                    'title'    => $titles[$title_ctr],
                    'status'   => 'legitimate'
                ];
                $overall_ctr += 1;
                if($team->isFromCamSur())
                    $location_ctr += 1;
            }
        }
        $title_ctr += sizeof($group);
        $overall[] = $group;

        if($overall_ctr >= sizeof($titles)) {
            if($location_ctr < LOCATION_QUOTA) {
                // when location quota is not met...

                // remove teams to give way to location quota
                $outplaced_ctr = LOCATION_QUOTA - $location_ctr;
                for($k = (sizeof($overall) - 1); $k >= 0; $k--) {
                    $group = $overall[$k];
                    for($l = 0; $l < sizeof($group); $l++) {
                        $member = $group[$l];
                        if(!$teams[$member['team_key']]->isFromCamSur()) {
                            $overall[$k][$l]['title']  = '';
                            $overall[$k][$l]['status'] = 'outplaced';
                            $outplaced_ctr -= 1;
                        }
                    }

                    if($outplaced_ctr <= 0)
                        break;
                }

                // insert teams to fulfill location quota
                $needed_ctr = LOCATION_QUOTA - $location_ctr;
                $title_ctr   -= $needed_ctr;
                $overall_ctr -= $needed_ctr;
                $overall_inserted = [];
                for($k = ($i + 1); $k < sizeof($unique_final_fractional_ranks); $k++) {
                    $group = [];
                    foreach($teams as $team_key => $team) {
                        if($results[$team_key]['rank']['final']['fractional'] == $unique_final_fractional_ranks[$k]) {
                            if($team->isFromCamSur()) {
                                $group[] = [
                                    'team_key' => $team_key,
                                    'title'    => $titles[$title_ctr],
                                    'status'   => 'inserted'
                                ];

                                $overall_ctr += 1;
                            }
                        }
                    }

                    $title_ctr += sizeof($group);
                    $overall_inserted[] = $group;
                    $needed_ctr -= sizeof($group);
                    if($needed_ctr <= 0)
                        break;
                }
                if(sizeof($overall_inserted) > 0)
                    $overall = array_merge($overall, $overall_inserted);
            }
            break;
        }
    }

    // process winners
    $tops_unordered = [];
    for($i = 0; $i < sizeof($overall); $i++) {
        $group = $overall[$i];
        foreach($group as $member) {
            $results[$member['team_key']]['title'] = [
                'slot'   => $member['title'],
                'status' => $member['status']
            ];

            if(in_array($member['status'], ['legitimate', 'inserted']))
                $tops_unordered[] = $member['team_key'];
        }
    }

    // shuffle $tops_unordered (deterministic)
    mt_srand(385971462);
    shuffle($tops_unordered);

    // arrange for Top 10 Q&A
    $event_top10_qa = Event::findBySlug('top10-qa');
    for($i=0; $i<sizeof($tops_unordered); $i++) {
        $team_key = $tops_unordered[$i];
        $event_top10_qa->setTeamOrder($teams[$team_key], ($i + 1));
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
    <title>TOP 10 | <?= $competition_title ?></title>
</head>
<body>
    <div class="p-1">
        <table class="table table-bordered result">
            <thead class="bt">
                <tr class="table-secondary">
                    <th colspan="3" rowspan="2" class="text-center bt br bl bb">
                        <h2 class="m-0">TOP 10</h2>
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
                        <h5 class="m-0">SLOT</h5>
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
                <tr<?= $team['title']['status'] != '' ? ' class="table-' . COLOR_CODE[$team['title']['status']] . '"' : '' ?>>
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

                    <!-- slot -->
                    <td class="bb br text-center">
                        <h5 class="m-0 fw-bold">
                            <?= $team['title']['slot'] ?>
                        </h5>
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
                                <div class="ms-5">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-secondary text-center">COLOR CODE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach(COLOR_CODE as $status => $color_class) { ?>
                                                <tr>
                                                    <td class="table-<?= $color_class ?>" style="width: 36px; height: 5px; border: 1px solid #eee"></td>
                                                    <td><small class="text-uppercase text-secondary"><?= $status ?></small></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
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


        <!-- Summary -->
        <div class="container-fluid mt-5" style="page-break-before: always;">
            <div class="row justify-content-center">
                <!-- unordered -->
                <div class="col-md-7" align="center">
                    <h4 class="opacity-75"><?= $competition_title ?></h4>
                    <h1>TOP <?= sizeof($titles) ?> in Random Order</h1>
                    <div style="width: 80%;">
                        <table class="table table-bordered mt-3">
                            <tbody>
                            <?php
                            foreach($tops_unordered as $team_key) {
                                $team = $results[$team_key];
                                ?>
                                <tr>
                                    <!-- number -->
                                    <td class="pe-3 fw-bold text-center">
                                        <h2 class="m-0 fw-bold">
                                            <?= $team['info']['number'] ?>
                                        </h2>
                                    </td>

                                    <!-- avatar -->
                                    <td style="width: 88px;">
                                        <img
                                            src="../../crud/uploads/<?= $team['info']['avatar'] ?>"
                                            alt="<?= $team['info']['number'] ?>"
                                            style="width: 100%; border-radius: 100%"
                                        >
                                    </td>

                                    <!-- name -->
                                    <td>
                                        <h6 class="text-uppercase m-0"><?= $team['info']['name'] ?></h6>
                                        <small class="m-0"><?= $team['info']['location'] ?></small>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Judges (Page 2) -->
        <div class="container-fluid mt-5 pb-2">
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
</body>
</html>
