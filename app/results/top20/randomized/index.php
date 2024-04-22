<?php
const LOGIN_PAGE_PATH = '../../../crud/';
require_once '../../../crud/auth.php';

require_once '../../../config/database.php';
require_once '../../../models/Admin.php';
require_once '../../../models/Team.php';
require_once '../../../models/Event.php';

// involved events
const EVENTS = [
    [
        'slug'    => 'screening-1',
        'percent' => 100
    ]
];

// initialize titles
$titles = [
    'Top 01',
    'Top 02',
    'Top 03',
    'Top 04',
    'Top 05',
    'Top 06',
    'Top 07',
    'Top 08',
    'Top 09',
    'Top 10',
    'Top 11',
    'Top 12',
    'Top 13',
    'Top 14',
    'Top 15',
    'Top 16',
    'Top 17',
    'Top 18',
    'Top 19',
    'Top 20',
];

// initialize admin
$admin = new Admin();

// initialize events and tabulate results
$events  = [];
$results = [];
$competition_title = '';
$category_title = '';
$judges        = [];
$judge_ids     = [];
$technicals    = [];
$technical_ids = [];
for($i=0; $i<sizeof(EVENTS); $i++) {
    $events[]  = Event::findBySlug(EVENTS[$i]['slug']);
    $results[] = $admin->tabulate($events[$i]);
    $category_title = $events[$i]->getCategory()->getTitle();

    if($i == 1)
        $competition_title = $events[$i]->getCategory()->getCompetition()->getTitle();

    $event_judges = $events[$i]->getAllJudges();
    for($j=0; $j<sizeof($event_judges); $j++) {
        $event_judge    = $event_judges[$j];
        $event_judge_id = $event_judge->getId();
        if(!in_array($event_judge_id, $judge_ids)) {
            $judges[]    = [ ...$event_judge->toArray(), 'is_chairman' =>  $event_judge->isChairmanOfEvent($events[$i]), 'event' => $events[$i]->getTitle()];
            $judge_ids[] = $event_judge_id;
        }
    }

    $event_technicals = $events[$i]->getAllTechnicals();
    for($j=0; $j<sizeof($event_technicals); $j++) {
        $event_technical    = $event_technicals[$j];
        $event_technical_id = $event_technical->getId();
        if(!in_array($event_technical_id, $technical_ids)) {
            $technicals[]    = $event_technical->toArray();
            $technical_ids[] = $event_technical_id;
        }
    }
}

// process result
$result = [];
$unique_total_rank_ave_equivs = [];
$unique_total_percentages = [];
$unique_adjusted_ranks = [];
foreach($events[0]->getAllTeams() as $team) {
    $team_key = 'team_'.$team->getId();
    $t = [
        'info'    => $team->toArray(),
        'inputs'  => [],
        'average' => 0,
        'rank'    => [
            'total'    => 0,
            'dense'    => 0,
            'initial'  => 0,
            'adjusted' => 0,
            'final' => [
                'dense'      => 0,
                'fractional' => 0
            ]
        ],
        'title' => ''
    ];

    // get rank and average
    for($i=0; $i<sizeof(EVENTS); $i++) {
        $r = [
            'average'        => 0,
            'average_equiv'  => 0,
            'rank'           => 0,
            'rank_ave'       => 0,
            'rank_ave_equiv' => 0
        ];
        if(isset($results[$i]['teams'][$team_key])) {
            $r['average']        = $results[$i]['teams'][$team_key]['ratings']['average'];
            $r['average_equiv']  = $r['average'] * (EVENTS[$i]['percent'] / 100.0);
            $r['rank']           = $results[$i]['teams'][$team_key]['rank']['final']['fractional'];
            $r['rank_ave']       = 100 - $r['rank'];
            $r['rank_ave_equiv'] = $r['rank_ave'] * (EVENTS[$i]['percent'] / 100.0);
        }

        // append $r to $t['inputs']
        $t['inputs'][EVENTS[$i]['slug']] = $r;

        // accumulate totals
        $t['average'] += $r['average_equiv'];
        $t['rank']['total'] += $r['rank_ave_equiv'];
    }

    // push $t['rank']['total'] to $unique_total_rank_ave_equivs
    if(!in_array($t['rank']['total'], $unique_total_rank_ave_equivs))
        $unique_total_rank_ave_equivs[] = $t['rank']['total'];

    // append $t to $result
    $result[$team_key] = $t;
}

// sort $unique_total_rank_ave_equivs
rsort($unique_total_rank_ave_equivs);

// gather $rank_group (for getting fractional rank)
$rank_group = [];
foreach($result as $team_key => $team) {
    // get dense rank
    $dense_rank = 1 + array_search($team['rank']['total'], $unique_total_rank_ave_equivs);
    $result[$team_key]['rank']['dense'] = $dense_rank;

    // push $team_key to $rank_group
    $key_rank = 'rank_' . $dense_rank;
    if(!isset($rank_group[$key_rank]))
        $rank_group[$key_rank] = [];
    $rank_group[$key_rank][] = $team_key;
}

// get initial fractional rank
$ctr = 0;
for($i = 0; $i < sizeof($unique_total_rank_ave_equivs); $i++) {
    $key = 'rank_' . ($i + 1);
    $group = $rank_group[$key];
    $size = sizeof($group);
    $initial_rank = $ctr + ((($size * ($size + 1)) / 2) / $size);

    // write $fractional_rank to $group members
    for($j = 0; $j < $size; $j++) {
        $result[$group[$j]]['rank']['initial'] = $initial_rank;

        // compute adjusted average
        $adjusted_rank = $initial_rank - ($result[$group[$j]]['average'] * 0.01);
        $result[$group[$j]]['rank']['adjusted'] = $adjusted_rank;

        // push to $unique_adjusted_ranks
        if(!in_array($adjusted_rank, $unique_adjusted_ranks))
            $unique_adjusted_ranks[] = $adjusted_rank;
    }

    $ctr += $size;
}

// sort $unique_adjusted_ranks
sort($unique_adjusted_ranks);

// gather $rank_group (for getting fractional rank)
$rank_group = [];
foreach($result as $team_key => $team) {
    // get dense rank
    $dense_rank = 1 + array_search($team['rank']['adjusted'], $unique_adjusted_ranks);
    $result[$team_key]['rank']['final']['dense'] = $dense_rank;

    // push $key to $rank_group
    $key_rank = 'rank_' . $dense_rank;
    if(!isset($rank_group[$key_rank]))
        $rank_group[$key_rank] = [];
    $rank_group[$key_rank][] = $team_key;
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
        $result[$group[$j]]['rank']['final']['fractional'] = $final_fractional_rank;
    }

    $ctr += $size;
}

// sort $unique_final_fractional_ranks
sort($unique_final_fractional_ranks);

// determine tops
$tops_ordered   = [];
$tops_unordered = [];
$i = 0;
foreach($titles as $title) {
    // update title of $unique_final_fractional_ranks[$i]'th team
    foreach($result as $team_key => $team) {
        if($team['rank']['final']['fractional'] == $unique_final_fractional_ranks[$i]) {
            $result[$team_key]['title'] = $titles[$i];
            $tops_ordered[]   = $team['info']['id'];
            $tops_unordered[] = $team['info']['id'];
        }
    }

    $i += 1;
    if($i >= sizeof($unique_final_fractional_ranks))
        break;
}

// initialize arrays for segregation
$winners = [];
$nonWinners = [];

foreach ($result as $team_key => $team) {
    if ($team['title'] !== '') {
        $winners[] = $team;
    } else {
        $nonWinners[] = $team;
    }
}

// shuffle the non-winners to randomize order
shuffle($nonWinners);

// shuffle the winners to randomize order
shuffle($winners);

// merge winners and non-winners
$finalOrderTeams = array_merge($winners);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../../crud/dist/bootstrap-5.2.3/css/bootstrap.min.css">
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
        .bb, table.result tbody tr:last-child td {
            border-bottom: 2px solid #aaa !important;
        }
        .bl {
            border-left: 2px solid #aaa !important;
        }
    </style>
    <title>Top <?= sizeof($titles) ?> (Randomized) | <?= $category_title ?></title>
</head>
<body>
<div class="p-1">
    <table class="table table-bordered result">
        <thead class="bt">
        <tr class="table-secondary">
            <th colspan="3" rowspan="3" class="text-center bt br bl bb">
                <h1 class="m-0">TOP <?= sizeof($titles) ?> (RANDOMIZED)</h1>
                <h5><?= $category_title ?></h5>
            </th>
            <?php for($i=0; $i<sizeof($events); $i++) { ?>
                <th colspan="3" class="text-center text-success bt br">
                    <h5 class="m-0"><?= $events[$i]->getTitle() ?></h5>
                </th>
            <?php } ?>
            <th rowspan="3" class="text-center bl bt br bb">
                TOTAL
            </th>
            <th rowspan="3" class="text-center bl bt br bb">
                <span class="opacity-50">INITIAL<br>RANK</span>
            </th>
            <th rowspan="3" class="text-center bl bt br bb">
                FINAL<br>RANK
            </th>
            <th rowspan="3" class="text-center bl bt br bb">
                SLOT
            </th>
        </tr>
        <tr class="table-secondary">
            <?php for($i=0; $i<sizeof(EVENTS); $i++) { ?>
                <th colspan="2" class="text-center bl"><span class="opacity-75">Average</span></th>
                <th rowspan="2" class="bb br text-center"><h5 class="m-0"><?= EVENTS[$i]['percent'] ?>%</h5></th>
            <?php } ?>
        </tr>
        <tr class="table-secondary">
            <?php for($i=0; $i<sizeof(EVENTS); $i++) { ?>
                <th colspan="2" class="bb text-center">Rank / Equiv.</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($finalOrderTeams as $team_key => $team) { ?>
            <tr<?= $team['title'] !== '' ? ' class="table-warning"' : '' ?>>
                <!-- number -->
                <td rowspan="2" class="pe-3 fw-bold bl bb" align="right">
                    <h3 class="m-0">
                        <?= $team['info']['number'] ?>
                    </h3>
                </td>

                <!-- avatar -->
                <td rowspan="2" class="bb" style="width: 56px;">
                    <img
                        src="../../../crud/uploads/<?= $team['info']['avatar'] ?>"
                        alt="<?= $team['info']['number'] ?>"
                        style="width: 56px; border-radius: 100%"
                    >
                </td>

                <!-- name -->
                <td rowspan="2" class="br bb">
                    <p class="mt-0 me-0 mb-1 ms-0 text-subtitle-2 text-uppercase font-weight-bold" style="line-height: 1.2"><b><?= $team['info']['name'] ?></b></p>
                    <p class="mb-0" style="line-height: 1; opacity: 0.8"><small><b><?= $team['info']['location'] ?></b></small></p>
                    <p class="mb-0" style="line-height: 1; opacity: 0.85"><small><?= $team['info']['meta'] ?></small></p>
                </td>

                <!-- averages -->
                <?php for($i=0; $i<sizeof(EVENTS); $i++) { ?>
                    <td colspan="2" class="pe-3" align="right"><span class="opacity-75"><?= number_format($team['inputs'][EVENTS[$i]['slug']]['average'], 2) ?></span></td>
                    <td align="right" class="pe-3 br text-secondary fw-bold"><span class="opacity-75"><?= number_format($team['inputs'][EVENTS[$i]['slug']]['average_equiv'], 2) ?></span></td>
                <?php } ?>

                <!-- total average -->
                <td class="br pe-3 text-secondary fw-bold" align="right"><?= number_format($team['average'], 2) ?></td>

                <!-- initial rank (spacer) -->
                <td class="br"></td>

                <!-- final rank (spacer) -->
                <td class="br"></td>

                <!-- slot -->
                <td rowspan="2" class="bb br text-center" style="line-height: 1.1; vertical-align: center">
                    <h4 class="m-0"><?= $team['title'] ?></h4>
                </td>
            </tr>

            <tr<?= $team['title'] !== '' ? ' class="table-warning"' : '' ?>>
                <?php for($i=0; $i<sizeof($events); $i++) { ?>
                    <td align="right" class="bb pe-3 text-primary"><?= number_format($team['inputs'][EVENTS[$i]['slug']]['rank'], 2) ?></td>
                    <td align="right" class="bb pe-3 text-primary"><span class="opacity-75"><?= number_format($team['inputs'][EVENTS[$i]['slug']]['rank_ave'], 2) ?></span></td>
                    <td align="right" class="bb br pe-3 text-primary fw-bold"><span class="opacity-75"><?= number_format($team['inputs'][EVENTS[$i]['slug']]['rank_ave_equiv'], 2) ?></span></td>
                <?php } ?>

                <!-- total rank -->
                <td class="br bb pe-3 text-primary fw-bold" align="right"><?= number_format($team['rank']['total'], 2) ?></td>

                <!-- initial rank -->
                <td class="br bb pe-3 fw-bold" align="right"><h5 class="m-0 opacity-50"><?= number_format($team['rank']['initial'], 2) ?></h5></td>

                <!-- final rank -->
                <td class="br bb pe-3 fw-bold" align="right"><h5 class="m-0"><?= number_format($team['rank']['final']['fractional'], 2) ?></h5></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!-- Judges -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <?php foreach($judges as $judge) { ?>
                <div class="col-4 col-sm-4 col-md-4">
                    <div class="mt-5 pt-4 text-center">
                        <h5 class="mb-0"><?= $judge['name'] ?></h5>
                    </div>
                    <div class="text-center">
                        <p class="mb-0">
                            Judge <?= $judge['number'] ?>
                            <?php if($judge['is_chairman']) { ?>
                                * (Chairman)
                            <?php } ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

</div>

<script src="../../../crud/dist/bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>