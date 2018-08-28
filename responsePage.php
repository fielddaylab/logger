<?php
// Indicate JSON data type
header('Content-Type: application/json');

// Establish the database connection
include "database.php";
include "regression.phar";

require_once "KMeans/Space.php";
require_once "KMeans/Point.php";
require_once "KMeans/Cluster.php";

require_once "PCA/pca.php";

use MCordingley\Regression\Algorithm\GradientDescent\Batch;
use MCordingley\Regression\Algorithm\GradientDescent\Schedule\Adam;
use MCordingley\Regression\Algorithm\GradientDescent\Gradient\Logistic as LogisticGradient;
use MCordingley\Regression\Algorithm\GradientDescent\StoppingCriteria\GradientNorm;
use MCordingley\Regression\Observations;
use MCordingley\Regression\Predictor\Logistic as LogisticPredictor;
use MCordingley\LinearAlgebra\Matrix;

ini_set('memory_limit','1024M');
ini_set('max_execution_time', 3000);

$db = connectToDatabase(DBDeets::DB_NAME_DATA);
if ($db->connect_error) {
    http_response_code(500);
    die('{ "errMessage": "Failed to Connect to DB." }');
}

function average($arr) {
    $filtered = array_filter($arr, function($val) { return !is_string($val) && isset($val); });
    $total = array_sum($filtered);
    $length = count($filtered);
    return ($length > 0) ? $total / $length : 'NaN';
}

function normalize($X) {
    $columns = array_map(null, ...$X);
    $minX = array();
    $maxX = array();
    foreach($columns as $i=>$feature) {
        $minX[$i] = min(...$feature);
        $maxX[$i] = max(...$feature);
    }
    $normalX = array();
    foreach ($X as $i=>$obs) {
        foreach ($X[$i] as $j=>$feature) {
            if ($j === 0) {
                $normalX[$i][$j] = $feature;
                continue;
            }
            if ($maxX[$j] !== $minX[$j]) {
                $normalX[$i][$j] = ($feature - $minX[$j]) / ($maxX[$j] - $minX[$j]);
            } else {
                $normalX[$i][$j] = 0;
            }
        }
    }
    return $normalX;
}

function replaceNans($arr) {
    $newArr = $arr;
    if (is_array($arr)) {
        foreach ($newArr as $i=>$val) {
            if (is_array($val)) {
                $newArr[$i] = replaceNans($newArr[$i]);
            } else if (!is_string($val) && is_nan($val)) {
                $newArr[$i] = 'NaN';
            } else if (!is_string($val) && is_infinite($val)) {
                $newArr[$i] = 'Inf';
            }
        }
    }
    return $newArr;
}

function covariance($coeff, $X) {
    // covariance matrix S = (X^T*VX)^-1
    $predictor = new LogisticPredictor($coeff);
    $numSamples = count($X);

    $Xrows = array();
    for ($i = 0; $i < $numSamples; $i++) {
        $Xrows[$i] = implode(' ', $X[$i]);
    }
    $groups = array_count_values($Xrows);

    $rows = count($groups);
    $V = array_fill(0, $rows, array_fill(0, $rows, 0)); // V initialized to rxr matrix of 0s

    $groupXs = array();
    // V is an r x r diagonal matrix whose elements are ni*pi*(1-pi)
    for ($i = 0; $i < $rows; $i++) {
        $groupKeys = array_keys($groups);
        $groupX = array_map('floatval', explode(' ', $groupKeys[$i]));
        $groupXs[$i] = $groupX;

        $n = $groups[$groupKeys[$i]]; // number of occurrences of the group
        $p = $predictor->predict($groupX);
        $V[$i][$i] = $n * $p * (1 - $p);
    }
    $Xm = new Matrix($groupXs);
    $Vm = new Matrix($V);
    $XTm = $Xm->transpose();
    $S = (($XTm->multiplyMatrix($Vm))->multiplyMatrix($Xm))->inverse();
    return $S;
}

function stdErrs($S) {
    $stdErrs = array();
    $cols = count($S);
    for ($i = 0; $i < $cols; $i++) {
        $stdErrs[$i] = sqrt($S[$i][$i]);
    }

    return $stdErrs;
}

function waldSq($b, $seb) {
    return pow(($b / $seb), 2);
}

function isSignificant($coeff, $stdErrs) {
    // array of upper tail critical chi square values for n df at 0.95 alpha
    $critValues = array(
        null, // null value for 0 df
        3.841,
        5.991,
        7.815,
        9.488,
        11.070,
        12.592,
        14.067,
        15.507,
        16.919,
        18.307,
        19.675,
        21.026,
        22.362,
        23.685,
        24.996,
        26.296,
        27.587,
        28.869,
        30.144,
        31.410,
        32.671,
        33.924,
        35.172,
        36.415,
        37.652,
        38.885,
        40.113,
        41.337,
        42.557,
        43.773,
        44.985,
        46.194,
        47.400,
        48.602,
        49.802,
        50.998,
        52.192,
        53.384,
        54.572,
        55.758
    );
    $n = count($coeff);
    $df = $n - 1;
    $isSignificant = array();
    $chiSqValues = array();
    for ($i = 0; $i < $n; $i++) {
        $chiSqValue = waldSq($coeff[$i], $stdErrs[$i]);
        $chiSqValues[$i] = $chiSqValue;
        $isSignificant[$i] = ($chiSqValue >= $critValues[$df]);
    }
    return array('isSignificant'=>$isSignificant, 'chiSqValues'=>$chiSqValues, 'critValue'=>$critValues[$df]);
}

if (isset($_GET['gameID'])) {
    $returned;
    if (isset($_GET['sessionID'])) {
        if (isset($_GET['level'])) {
            $returned = getAndParseData(null, $_GET['gameID'], $db, $_GET['sessionID'], $_GET['level']);
        } else {
            $returned = getAndParseData(null, $_GET['gameID'], $db, $_GET['sessionID'], null);
        }
    } else {
        if (isset($_GET['column'])) {
            $returned = getAndParseData($_GET['column'], $_GET['gameID'], $db, null, null);
        } else {
            $returned = getAndParseData(null, $_GET['gameID'], $db, null, null);
        }
        
    }
    //echo print_r($returned);
    $output = json_encode(replaceNans($returned));
    if ($output) {
        echo $output;
    } else {
        http_response_code(500);
        die('{ "error": "'.json_last_error_msg().'"}');
    }
}

function getTotalNumSessions($gameID, $db) {
    $query = "SELECT COUNT(session_id) FROM (SELECT DISTINCT session_id FROM log WHERE app_id=?) q;";
    $params = array($gameID);
    $stmt = queryMultiParam($db, $query, 's', $params);
    if($stmt === NULL) {
        http_response_code(500);
        die('{ "errMessage": "Error running query." }');
    }
    if (!$stmt->bind_result($numSessions)) {
        http_response_code(500);
        die('{ "errMessage": "Failed to bind to results." }');
    }
    $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
    $allEvents = array();
    $stmt->fetch();
    $stmt->close();
    return $numSessions;
}

function array_column_fixed($input, $column_key) {
    $output = [];
    foreach ($input as $k => $v) {
        if (isset($v[$column_key])) {
            $output[$k] = $v[$column_key];
        }
    }
    return $output;
}

function analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column) {
    $startLevel = $_GET['minLevels'];
    $endLevel = $_GET['maxLevels'];
    $sessionIDs = $sessionsAndTimes['sessions'];
    $allData = array();
    // arrays of arrays (temp)
    $levelTimesPerLevelAll = array();
    $numMovesPerLevelAll = array();
    $moveTypeChangesPerLevelAll = array();
    $knobStdDevsPerLevelAll = array();
    $knobTotalAmtsPerLevelAll = array();
    $knobAvgsPerLevelAll = array();

    // arrays of totals of above arrays (temp)
    $totalTimesPerLevelAll = array();
    $totalMovesPerLevelArray = array();
    $totalMoveTypeChangesPerLevelAll = array();
    $totalStdDevsPerLevelAll = array();
    $totalKnobTotalsPerLevelAll = array();
    $totalKnobAvgsPerLevelAll = array();

    // scalar totals of totals arrays (temp)
    $totalTimeAll = 0;
    $totalMovesAll = 0;
    $totalMoveTypeChangesAll = 0;
    $totalStdDevsAll = 0;
    $totalKnobTotalsAll = 0;
    $totalKnobAvgsAll = 0;

    // arrays of averages per level (display)
    $avgLevelTimesAll = array();
    $avgMovesArray = array();
    $avgMoveTypeChangesPerLevelAll = array();
    $avgStdDevsPerLevelAll = array();
    $avgKnobTotalsPerLevelAll = array();
    $avgKnobAvgsPerLevelAll = array();

    // scalar averages of averages arrays (display)
    $avgTimeAll = 0;
    $avgMovesAll = 0;
    $avgMoveTypeChangesAll = 0;
    $avgStdDevAll = 0;
    $avgKnobTotalsAll = 0;
    $avgKnobAvgsAll = 0;

    foreach ($levels as $i) {
        $levelTimesPerLevelAll[$i] = array();
        $moveTypeChangesPerLevelAll[$i] = array();
        $numMovesPerLevelAll[$i] = array();
        $knobStdDevsPerLevelAll[$i] = array();
        $knobTotalAmtsPerLevelAll[$i] = array();
        $knobAvgsPerLevelAll[$i] = array();
    }
    foreach ($sessionIDs as $s=>$sessionID) {
        $infoTimes = array();
        $infoEventData = array();
        $infoLevels = array();
        $infoEvents = array();
        foreach ($sessionAttributes[$sessionID] as $i=>$val) {
            $infoTimes []= $val['time'];
            $infoEventData []= $val['event_data_complex'];
            $infoLevels []= $val['level'];
            $infoEvents []= $val['event'];
        }
        $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels);
        $avgTime;
        $totalTime = 0;
        $numMovesPerChallenge;
        $totalMoves = 0;
        $avgMoves;
        $moveTypeChangesPerLevel;
        $moveTypeChangesTotal = 0;
        $moveTypeChangesAvg;
        $knobStdDevs;
        $knobNumStdDevs;
        $knobAmtsTotal = 0;
        $knobAmtsAvg;
        $knobSumTotal = 0;
        $knobSumAvg;
        $numLevelsThisSession2 = count(array_unique($dataObj['levels']));
        if (isset($dataObj['times'])) {
            // Basic features stuff
            $levelStartTime;
            $levelEndTime;
            $lastSlider = null;
            $startIndices = array();
            $endIndices = array();
            $moveTypeChangesPerLevel = array();
            $knobStdDevs = array();
            $knobNumStdDevs = array();
            $knobAmts = array();
            $numMovesPerChallenge = array();
            $moveTypeChangesPerLevel = array();
            $knobStdDevs = array();
            $knobNumStdDevs = array();
            $startIndices = array();
            $endIndices = array();
            $indicesToSplice = array();
            $levelTimes = array();
            $avgKnobStdDevs = array();
            $knobAvgs = array();
            foreach ($dataObj['levels'] as $i) {
                $numMovesPerChallenge[$i] = array();
                $indicesToSplice[$i] = array();

                $startIndices[$i] = null;
                $endIndices[$i] = null;
                $moveTypeChangesPerLevel[$i] = 0;
                $knobStdDevs[$i] = 0;
                $knobNumStdDevs[$i] = 0;
                $knobAmts[$i] = 0;
                $knobAvgs[$i] = 0;
                $avgKnobStdDevs[$i] = 0;
            }

            for ($i = 0; $i < count($dataObj['times']); $i++) {
                if (!isset($endIndices[$dataObj['levels'][$i]])) {
                    $dataJson = json_decode($dataObj['data'][$i], true);
                    if ($dataObj['events'][$i] === 'BEGIN') {
                        if (!isset($startIndices[$dataObj['levels'][$i]])) { // check this space isn't filled by a previous attempt on the same level
                            $startIndices[$dataObj['levels'][$i]] = $i;
                        }
                    } else if ($dataObj['events'][$i] === 'COMPLETE') {
                        if (!isset($endIndices[$dataObj['levels'][$i]])) {
                            $endIndices[$dataObj['levels'][$i]] = $i;
                        }
                    } else if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE')) {
                        if ($lastSlider !== $dataJson['slider']) {
                            if (!isset($moveTypeChangesPerLevel[$dataObj['levels'][$i]])) $moveTypeChangesPerLevel[$dataObj['levels'][$i]] = 0;
                            $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                        }
                        $lastSlider = $dataJson['slider'];
                        $numMovesPerChallenge[$dataObj['levels'][$i]] []= $i;
                        //if (!isset($knobNumStdDevs[$dataObj['levels'][$i]])) $knobNumStdDevs[$dataObj['levels'][$i]] = 0;
                        $knobNumStdDevs[$dataObj['levels'][$i]]++;
                        //if (!isset($knobStdDevs[$dataObj['levels'][$i]])) $knobStdDevs[$dataObj['levels'][$i]] = 0;
                        $knobStdDevs[$dataObj['levels'][$i]] += $dataJson['stdev_val'];
                        //if (!isset($knobAmts[$dataObj['levels'][$i]])) $knobAmts[$dataObj['levels'][$i]] = 0;
                        $knobAmts[$dataObj['levels'][$i]] += ($dataJson['max_val']-$dataJson['min_val']);
                    }
                }
            }

            foreach ($endIndices as $i=>$value) {
                if (isset($endIndices[$i], $dataObj['times'][$endIndices[$i]], $dataObj['times'][$startIndices[$i]])) {
                    $levelStartTime = new DateTime($dataObj['times'][$startIndices[$i]]);
                    $levelEndTime = new DateTime($dataObj['times'][$endIndices[$i]]);
                    $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                    $totalTime += $levelTime;
                    $levelTimes[$i] = $levelTime;

                    $totalMoves += count($numMovesPerChallenge[$i]);
                    $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];

                    $knobAvgAmt = 0;
                    $knobAvgStdDev = 0;
                    if ($knobNumStdDevs[$i] != 0) {
                        $temp = $knobAmts[$i]/$knobNumStdDevs[$i];
                        $knobAmtsTotal += $temp;
                        $knobAvgAmt = $temp;
                        $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                    }
                    $knobAvgs[$i] = $knobAvgAmt;
                    $avgKnobStdDevs[$i] = $knobAvgStdDev;

                    if ($knobAmts[$i] != 0) {
                        $knobSumTotal += $knobAmts[$i];
                    }
                }
            }
            $avgTime = $totalTime / $numLevelsThisSession2;
            $avgMoves = $totalMoves / $numLevelsThisSession2;
            $moveTypeChangesAvg = $moveTypeChangesTotal / $numLevelsThisSession2;
            $knobAmtsAvg = $knobAmtsTotal / $numLevelsThisSession2;
            $knobSumAvg = $knobSumTotal / $numLevelsThisSession2;
        }
        $numMoves = array();
        $filteredNumMoves = array_filter($numMovesPerChallenge, function ($value) { return isset($value); });
        foreach ($filteredNumMoves as $j=>$value) {
            $numMoves[$j] = count($numMovesPerChallenge[$j]);
        }
        $allData[$sessionID] = array('levelTimes'=>$levelTimes, 'avgTime'=>$avgTime, 'totalTime'=>$totalTime, 'numMovesPerChallenge'=>$numMoves, 'totalMoves'=>$totalMoves,
        'avgMoves'=>$avgMoves, 'moveTypeChangesPerLevel'=>$moveTypeChangesPerLevel, 'moveTypeChangesTotal'=>$moveTypeChangesTotal, 'moveTypeChangesAvg'=>$moveTypeChangesAvg,
        'knobStdDevs'=>$avgKnobStdDevs, 'knobNumStdDevs'=>$knobNumStdDevs, 'knobAvgs'=>$knobAvgs, 'knobAmtsTotalAvg'=>$knobAmtsTotal, 'knobAmtsAvgAvg'=>$knobAmtsAvg,
        'knobTotalAmts'=>$knobAmts, 'knobSumTotal'=>$knobSumTotal, 'knobTotalAvg'=>$knobSumAvg, 'numMovesPerChallengeArray'=>$numMovesPerChallenge, 'dataObj'=>$dataObj,
        'numLevels'=>count($levelTimes));
    }

    // loop through all the sessions we got above and add their variables to totals
    $timeCol = array_column($allData, 'levelTimes');
    $moveCol = array_column($allData, 'numMovesPerChallenge');
    $levelsCol = array_column($allData, 'numLevels');
    $typeCol = array_column($allData, 'moveTypeChangesPerLevel');
    $stdCol = array_column($allData, 'knobStdDevs');
    $totalCol = array_column($allData, 'knobTotalAmts');
    $avgCol = array_column($allData, 'knobAvgs');

    $newArray = array();
    $a = array_column($allEvents, 'level');
    $b = array_column($allEvents, 'event');

    for ($i = $levels[0]; $i <= end($levels); $i++) {
        $totalTimesPerLevelAll[$i] = average(array_column($timeCol, $i));
        $totalMovesPerLevelArray[$i] = average(array_column($moveCol, $i));
        $totalMoveTypeChangesPerLevelAll[$i] = average(array_column($typeCol, $i));
        $totalStdDevsPerLevelAll[$i] = average(array_column($stdCol, $i));
        $totalKnobTotalsPerLevelAll[$i] = average(array_column($totalCol, $i));
        $totalKnobAvgsPerLevelAll[$i] = average(array_column($avgCol, $i));
    }
    $totalTimeAll = array_sum($totalTimesPerLevelAll);
    $totalMovesAll = array_sum($totalMovesPerLevelArray);
    $totalMoveTypeChangesAll = array_sum($totalMoveTypeChangesPerLevelAll);
    //$totalStdDevsAll = sum($totalStdDevsPerLevelAll);
    $totalKnobTotalsAll = array_sum($totalKnobTotalsPerLevelAll);
    $totalKnobAvgsAll = array_sum($totalKnobAvgsPerLevelAll);

    $avgTimeAll = average($totalTimesPerLevelAll);
    $avgMovesAll = average($totalMovesPerLevelArray);
    $avgMoveTypeChangesAll = average($totalMoveTypeChangesPerLevelAll);
    //$avgStdDevAll = average($totalStdDevsPerLevelAll);
    $avgKnobTotalsAll = average($totalKnobTotalsPerLevelAll);
    $avgKnobAvgsAll = average($totalKnobAvgsPerLevelAll);

    $basicInfoAll = array('times'=>$totalTimesPerLevelAll, 'numMoves'=>$totalMovesPerLevelArray, 'moveTypeChanges'=>$totalMoveTypeChangesPerLevelAll,
        'knobStdDevs'=>$totalStdDevsPerLevelAll, 'totalMaxMin'=>$totalKnobTotalsPerLevelAll, 'avgMaxMin'=>$totalKnobAvgsPerLevelAll,
        'totalTime'=>$totalTimeAll, 'totalMoves'=>$totalMovesAll, 'totalMoveChanges'=>$totalMoveTypeChangesAll,
        'totalKnobTotals'=>$totalKnobTotalsAll, 'totalKnobAvgs'=>$totalKnobAvgsAll,
        'avgTime'=>$avgTimeAll, 'avgMoves'=>$avgMovesAll, 'avgMoveChanges'=>$avgMoveTypeChangesAll,
        'avgKnobTotals'=>$avgKnobTotalsAll, 'avgKnobAvgs'=>$avgKnobAvgsAll);
    // Get questions histogram data
    $questionsCorrect = array();
    $questionsAnswered = array();
    $questionAnswereds = array();
    $totalCorrect = 0;
    $totalAnswered = 0;
    foreach ($sessionIDs as $i=>$val) {
        $questionEvents = array();
        foreach ($sessionAttributes[$val] as $j=>$jval) {
            if ($jval['event_custom'] === 3) {
                $questionEvents []= $jval;
            }
        }
        $numCorrect = 0;
        $numQuestions = count($questionEvents);
        for ($j = 0; $j < $numQuestions; $j++) {
            $jsonData = json_decode($questionEvents[$j]['event_data_complex'], true);
            $questionAnswereds[$i][$j] = $jsonData['answered'];
            if ($jsonData['answer'] === $jsonData['answered']) {
                $numCorrect++;
            }
        }
        $totalCorrect += $numCorrect;
        $totalAnswered += $numQuestions;
        $questionsCorrect[$i] = $numCorrect;
        $questionsAnswered[$i] = $numQuestions;
    }
    $questionsAll = array('numsCorrect'=>$questionsCorrect, 'numsQuestions'=>$questionsAnswered);
    $questionsTotal = array('totalNumCorrect'=>$totalCorrect, 'totalNumQuestions'=>$totalAnswered);

    // Get moves histogram data
    $numMovesAll = array();
    foreach ($sessionIDs as $i=>$session) {
        $numMoves = 0;
        foreach ($sessionAttributes[$session] as $j=>$val) {
            if ($val['event_custom'] === 1) {
                $numMoves++;
            }
        }
        $numMovesAll []= $numMoves;
    }

    // Get levels histogram data
    $numLevelsAll = array();
    $levelsCompleteAll = array();
    foreach ($sessionIDs as $i=>$session) {
        $levelsCompleted = array();
        foreach ($sessionAttributes[$session] as $j=>$val) {
            if ($val['event'] === 'COMPLETE') {
                $levelsCompleted[$val['level']] = true;
            }
        }
        $numLevelsAll[] = count($levelsCompleted);
        $levelsCompleteAll[$session] = $levelsCompleted;
    }

    foreach ($sessionIDs as $index=>$session) {
        $data = $allData[$session];
        $dataObj = $data['dataObj'];
        $sessionLevels = array_keys($data['numMovesPerChallenge']);
        foreach ($sessionLevels as $j=>$level) {
            $numMovesPerChallenge = $data['numMovesPerChallengeArray'][$level];
            $numMoves = $data['numMovesPerChallenge'][$level];

            $distanceToGoal1;
            $moveGoodness1;
            $absDistanceToGoal1;
            if (isset($numMovesPerChallenge)) {
                $absDistanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0);
                $distanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0); // this one is just -1/0/1
                $moveGoodness1 = array_fill(0, count($numMovesPerChallenge), 0); // an array of 0s
            }
            $moveNumbers = array();
            $cumulativeDistance1 = 0;

            foreach ($numMovesPerChallenge as $i=>$val) {
                $dataJson = json_decode($dataObj['data'][$i], true);
                if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE')) {
                    if ($dataJson['end_closeness'] < $dataJson['begin_closeness']) $moveGoodness1[$i] = 1;
                    else if ($dataJson['end_closeness'] > $dataJson['begin_closeness']) $moveGoodness1[$i] = -1;
                }
                $moveNumbers[$i] = $i;
                $cumulativeDistance1 += $moveGoodness1[$i];
                $distanceToGoal1[$i] = $cumulativeDistance1;
            }

            // Find % good moves by filtering moveGoodness array for 1s, aka good moves
            $numGoodMoves = count(array_filter($moveGoodness1, function($val) { return $val === 1; }));
            if ($numMoves > 1) {
                $percentGoodMoves = $numGoodMoves / $numMoves;
            } else {
                $percentGoodMoves = 1; // if they only had 1 move or somehow beat the level with 0, give them 100% good moves
            }
            $percentGoodMovesAll[$level][$index] = $percentGoodMoves;
        }
    }
    if (!isset($column)) {
        $lvlsPercentComplete = array();
        $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);

        foreach ($levelsForTable as $index=>$lvl) {
            $numComplete = 0;
            $numTotal = 0;
            foreach ($levelsCompleteAll as $j=>$session) {
                if (isset($session[$lvl]) && $session[$lvl]) {
                    $numComplete++;
                }
                $numTotal++;
            }
            $lvlsPercentComplete[] = $numComplete / $numTotal * 100;
        }
        return array('numLevelsAll'=>$numLevelsAll, 'numMovesAll'=>$numMovesAll, 'questionsAll'=>$questionsAll, 'basicInfoAll'=>$basicInfoAll,
            'sessionsAndTimes'=>$sessionsAndTimes, 'levels'=>$levels, 'numSessions'=>count($sessionsAndTimes['sessions']), 'questionsTotal'=>$questionsTotal,
            'lvlsPercentComplete'=>$lvlsPercentComplete ,'clusters'=>array('col1'=>$bestColumn1, 'col2'=>$bestColumn2, 'clusters'=>$clusterPoints, 'dunn'=>$bestDunn,
            'sourceColumns'=>$usedColumns, 'eigenvectors'=>$eigenvectors));
    }

    // Linear regression stuff
    $regressionVars = array();
    $intercepts = array();
    $coefficients = array();
    $stdErrs = array();
    $signifiances = array();
    if (!isset($reqSessionID) && !isset($_GET['predictTable'])) {
        $predictors = array();
        $predicted = array();
        $algorithm = new Batch(new LogisticGradient, new Adam, new GradientNorm);

        if ($column === 'level10' || $column === 'level20' || $column === 'gameComplete') {
            foreach ($sessionIDs as $i=>$val) {
                $percentQuestionsCorrect = ($questionsAll['numsQuestions'][$i] === 0) ? 0 : $questionsAll['numsCorrect'][$i] / $questionsAll['numsQuestions'][$i];
                // 1 is for the intercept
                $predictor = array(1, $numMovesAll[$i], array_sum($typeCol[$i]), $levelsCol[$i], array_sum($timeCol[$i]), array_sum($avgCol[$i]), $percentQuestionsCorrect);
                for ($j = $startLevel; $j <= $endLevel; $j++) {
                    //$predictor []= $percentGoodMovesAll[$j][$i];
                }

                if ($column === 'level10') {
                    $predicted []= ($numLevelsAll[$i] >= 9) ? 1 : 0;
                } else if ($column === 'level20') {
                    $predicted []= ($numLevelsAll[$i] >= 15) ? 1 : 0;
                } else {
                    $predicted []= ($numLevelsAll[$i] >= 28) ? 1 : 0;
                }

                $predictors []= $predictor;
            }
        } else { // column is one of the questions
            $predictors = array();
            $predicted = array();
            $quesIndex = intval(substr($column, 1, 1));
            $ansIndex = intval(substr($column, 2, 1));
            foreach ($questionAnswereds as $i=>$val) {
                if (isset($val[$quesIndex])) {
                    $numMoves = $numMovesAll[$i];
                    $numTypeChanges = array_sum($typeCol[$i]);
                    $time = array_sum($timeCol[$i]);
                    $minMax = array_sum($avgCol[$i]);
                    $percentQuestionsCorrect = ($questionsAll['numsQuestions'][$i] === 0) ? 0 : $questionsAll['numsCorrect'][$i] / $questionsAll['numsQuestions'][$i];
                    $predictors []= array(1, $numMovesAll[$i], array_sum($typeCol[$i]), $levelsCol[$i], array_sum($timeCol[$i]), array_sum($avgCol[$i]), $percentQuestionsCorrect);
                    $predicted []= ($val[$quesIndex] === $ansIndex) ? 1 : 0;
                }
            }
        }
        if (!empty($predictors) && !empty($predicted)) {
            $observations = Observations::fromArray($predictors, $predicted);
            $coefficients = $algorithm->regress($observations);
            $covariance = covariance($coefficients, $observations->getFeatures())->toArray();
            $stdErrs = stdErrs($covariance);
            $significances = isSignificant($coefficients, $stdErrs);
            $regressionVars []= array($observations->getFeatures(), $observations->getOutcomes());
        } else {
            $observations = $coefficients = $covariance = $stdErrs = $significances = null;
        }

        return array('regressionVars'=>$regressionVars, 'significances'=>$significances, 'equationVars'=>array('intercepts'=>$intercepts, 'coefficients'=>$coefficients, 'stdErrs'=>$stdErrs));
    } else if (isset($_GET['predictTable'])) {
        $predictors = array();
        $predicted = array();
        $algorithm = new Batch(new LogisticGradient, new Adam, new GradientNorm);
        $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);

        foreach ($sessionIDs as $i=>$val) {
            $percentQuestionsCorrect = ($questionsAll['numsQuestions'][$i] === 0) ? 0 : $questionsAll['numsCorrect'][$i] / $questionsAll['numsQuestions'][$i];
            // 1 is for the intercept
            $predictor = array(1, $numMovesAll[$i], array_sum($typeCol[$i]), $levelsCol[$i], array_sum($timeCol[$i]), array_sum($avgCol[$i]), $percentQuestionsCorrect);
            $colLvl = intval(substr($column, 3));
            foreach ($levelsForTable as $j=>$lvl) {
                if ($lvl >= $colLvl) break;
                $predictor []= $percentGoodMovesAll[$lvl][$i];
            }
            $predicted []= (isset($levelsCompleteAll[$val][$colLvl]) && $levelsCompleteAll[$val][$colLvl]) ? 1 : 0;

            $predictors []= $predictor;
        }

        if (!empty($predictors) && !empty($predicted)) {
            $observations = Observations::fromArray($predictors, $predicted);
            $coefficients = $algorithm->regress($observations);
            $covariance = covariance($coefficients, $observations->getFeatures())->toArray();
            $stdErrs = stdErrs($covariance);
            $significances = isSignificant($coefficients, $stdErrs);
            $regressionVars []= array($observations->getFeatures(), $observations->getOutcomes());
        } else {
            $observations = $coefficients = $covariance = $stdErrs = $significances = null;
        }

        return array('regressionVars'=>$regressionVars, 'significances'=>$significances, 'equationVars'=>array('intercepts'=>$intercepts, 'coefficients'=>$coefficients, 'stdErrs'=>$stdErrs));
    }
}

function getAndParseData($column, $gameID, $db, $reqSessionID, $reqLevel) {
    if (!isset($reqSessionID) && !isset($_GET['predictColumn'])) {
        $minMoves = $_GET['minMoves'];
        $startLevel = $_GET['minLevels'];
        $endLevel = $_GET['maxLevels'];
        $minQuestions = $_GET['minQuestions'];
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $maxRows = $_GET['maxRows'];

        $query = "SELECT a.session_id, a.level, a.event, a.event_custom, a.event_data_complex, a.client_time, a.app_id
        FROM log as a
        WHERE a.client_time>=? AND a.client_time<=? AND a.app_id=? ";
        $params = array($startDate, $endDate, $gameID);
        $paramTypes = 'sss';

        if ($minMoves > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT * FROM (
                        SELECT session_id, event_custom
                        FROM log
                        WHERE event_custom=1
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                    LIMIT ?) temp
                ) AS moves
            ) ";
            $params []= $minMoves;
            $params []= $maxRows;
            $paramTypes .= 'ii';
        }

        $distinctLevels = $endLevel - $startLevel + 1;
        $query .= "AND a.session_id IN
        (
            SELECT session_id FROM
            (
                SELECT * FROM (
                    SELECT DISTINCT level, session_id, event
                    FROM log
                    WHERE event='COMPLETE' AND level BETWEEN ? AND ?
                    GROUP BY session_id
                    HAVING COUNT(DISTINCT level) >= ?
                LIMIT ?) temp
            ) AS levels
        ) ";
        $params []= $startLevel;
        $params []= $endLevel;
        $params []= $distinctLevels;
        $params []= $maxRows;
        $paramTypes .= 'iiii';

        if ($minQuestions > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT * FROM (
                        SELECT session_id, event_custom
                        FROM log
                        WHERE event_custom=3
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                    LIMIT ?) temp
                ) AS questions
            ) ";
            $params []= $minQuestions;
            $params []= $maxRows;
            $paramTypes .= 'ii';
        }

        $query .= "ORDER BY a.client_time";

        $stmt = queryMultiParam($db, $query, $paramTypes, $params);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
        $allEvents = array();
        while($stmt->fetch()) {
            $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom,
            'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
            // Group the variables into their sessionIDs in a big associative array
            $sessionAttributes[$session_id][] = $tuple;
            // Also make one big array of every event for easier extraction of unique attributes
            $allEvents[] = $tuple;
        }
        $stmt->close();

        foreach ($sessionAttributes as $i=>$val) {
            uasort($sessionAttributes[$i], function($a, $b) {
                return ($a['time'] <= $b['time']) ? -1 : 1;
            });
        }

        // Sort session ids by date, the default from before
        uasort($sessionAttributes, function($a, $b) {
            return ($a[0]['time'] <= $b[0]['time']) ? -1 : 1;
        });

        $sessions = array_keys($sessionAttributes);
        $uniqueSessions = array_unique($sessions);
        $numSessions = count($uniqueSessions);

        $numEvents = count($allEvents);
        $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE'; });
        $completeLevels = array_column($completeEvents, 'level');
        $levels = array_filter(array_unique(array_column($allEvents, 'level')), function($a) use($completeLevels) { return in_array($a, $completeLevels); });
        sort($levels);
        $numLevels = count($levels);

        $times = array(); // Construct array of each session's first time
        foreach ($sessionAttributes as $i=>$val) {
            $times[$i] = $val[0]['time'];
        }

        // Construct sessions and times array
        $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

        $columnData = array();

        switch ($column) {
            case 'gameComplete':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'level10':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'level20':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q00':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q01':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q02':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q03':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q10':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q11':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q12':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q13':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q20':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q21':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q22':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q23':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q30':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q31':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q32':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            case 'q33':
                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
                break;
            default:
                // Do basic features and histograms on big data set here
                $query = "SELECT a.session_id, a.level, a.event, a.event_custom, a.event_data_complex, a.client_time, a.app_id
                FROM log as a
                WHERE a.client_time>=? AND a.client_time<=? AND a.app_id=? ";
                $params = array($startDate, $endDate, $gameID);
                $paramTypes = 'sss';

                if ($minMoves > 0) {
                    $query .= "AND a.session_id IN
                    (
                        SELECT session_id FROM
                        (
                            SELECT * FROM (
                                SELECT session_id, event_custom
                                FROM log
                                WHERE event_custom=1
                                GROUP BY session_id
                                HAVING COUNT(*) >= ?
                            LIMIT ?) temp
                        ) AS moves
                    ) ";
                    $params []= $minMoves;
                    $params []= $maxRows;
                    $paramTypes .= 'ii';
                }

                $distinctLevels = $endLevel - $startLevel + 1;
                $query .= "AND a.session_id IN
                (
                    SELECT session_id FROM
                    (
                        SELECT * FROM (
                            SELECT DISTINCT level, session_id, event
                            FROM log
                            WHERE event='COMPLETE' AND level BETWEEN ? AND ?
                            GROUP BY session_id
                            HAVING COUNT(DISTINCT level) >= ?
                        LIMIT ?) temp
                    ) AS levels
                ) ";
                $params []= $startLevel;
                $params []= $endLevel;
                $params []= $distinctLevels;
                $params []= $maxRows;
                $paramTypes .= 'iiii';

                if ($minQuestions > 0) {
                    $query .= "AND a.session_id IN
                    (
                        SELECT session_id FROM
                        (
                            SELECT * FROM (
                                SELECT session_id, event_custom
                                FROM log
                                WHERE event_custom=3
                                GROUP BY session_id
                                HAVING COUNT(*) >= ?
                            LIMIT ?) temp
                        ) AS questions
                    ) ";
                    $params []= $minQuestions;
                    $params []= $maxRows;
                    $paramTypes .= 'ii';
                }

                $query .= "ORDER BY a.client_time";

                $stmt = queryMultiParam($db, $query, $paramTypes, $params);
                if($stmt === NULL) {
                    http_response_code(500);
                    die('{ "errMessage": "Error running query." }');
                }
                if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
                    http_response_code(500);
                    die('{ "errMessage": "Failed to bind to results." }');
                }
                $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
                $allEvents = array();
                while($stmt->fetch()) {
                    $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom,
                    'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
                    // Group the variables into their sessionIDs in a big associative array
                    $sessionAttributes[$session_id][] = $tuple;
                    // Also make one big array of every event for easier extraction of unique attributes
                    $allEvents[] = $tuple;
                }
                $stmt->close();

                foreach ($sessionAttributes as $i=>$val) {
                    uasort($sessionAttributes[$i], function($a, $b) {
                    return ($a['time'] <= $b['time']) ? -1 : 1;
                    });
                }

                // Sort session ids by date, the default from before
                uasort($sessionAttributes, function($a, $b) {
                return ($a[0]['time'] <= $b[0]['time']) ? -1 : 1;
                });

                $sessions = array_keys($sessionAttributes);
                $uniqueSessions = array_unique($sessions);
                $numSessions = count($uniqueSessions);

                $numEvents = count($allEvents);
                $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE'; });
                $completeLevels = array_column($completeEvents, 'level');
                $levels = array_filter(array_unique(array_column($allEvents, 'level')), function($a) use($completeLevels) { return in_array($a, $completeLevels); });
                sort($levels);
                $numLevels = count($levels);

                $times = array(); // Construct array of each session's first time
                foreach ($sessionAttributes as $i=>$val) {
                    $times[$i] = $val[0]['time'];
                }

                // Construct sessions and times array
                $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

                $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, null);
                $totalNumSessions = getTotalNumSessions($gameID, $db);
                $columnData['totalNumSessions'] = $totalNumSessions;
                $columnData['numEvents'] = $numEvents;
                $columnData['numSessions'] = $numSessions;
                $columnData['numLevels'] = $numLevels;
                break;
        }

        $output = $columnData;

        // Return ALL the above information at once in a big array
        return replaceNans($output);

        // Main query that returns filtered data
        /*$query = "SELECT a.session_id, a.level, a.event, a.event_custom, a.event_data_complex, a.client_time, a.app_id
        FROM log as a
        WHERE a.client_time>=? AND a.client_time<=? AND a.app_id=? ";
        $params = array($startDate, $endDate, $gameID);
        $paramTypes = 'sss';

        if ($minMoves > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT * FROM (
                        SELECT session_id, event_custom
                        FROM log
                        WHERE event_custom=1
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                    LIMIT ?) temp
                ) AS moves
            ) ";
            $params []= $minMoves;
            $params []= $maxRows;
            $paramTypes .= 'ii';
        }

        $distinctLevels = $endLevel - $startLevel + 1;
        $query .= "AND a.session_id IN
        (
            SELECT session_id FROM
            (
                SELECT * FROM (
                    SELECT DISTINCT level, session_id, event
                    FROM log
                    WHERE event='COMPLETE' AND level BETWEEN ? AND ?
                    GROUP BY session_id
                    HAVING COUNT(DISTINCT level) >= ?
                LIMIT ?) temp
            ) AS levels
        ) ";
        $params []= $startLevel;
        $params []= $endLevel;
        $params []= $distinctLevels;
        $params []= $maxRows;
        $paramTypes .= 'iiii';

        if ($minQuestions > 0) {
            $query .= "AND a.session_id IN
            (
                SELECT session_id FROM
                (
                    SELECT * FROM (
                        SELECT session_id, event_custom
                        FROM log
                        WHERE event_custom=3
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                    LIMIT ?) temp
                ) AS questions
            ) ";
            $params []= $minQuestions;
            $params []= $maxRows;
            $paramTypes .= 'ii';
        }

        $query .= "ORDER BY a.client_time";*/

        // Cluster stuff
        /*if (!isset($reqSessionID)) {
            $clusterLevel = 4;
            $sourceColumns = [];
            $allColumns = [
                [array_column_fixed($moveCol, $clusterLevel), 'numMovesPerChallenge', [216]],
                [array_column_fixed($avgCol, $clusterLevel), 'knobAvgs', []],
                [array_column_fixed($timeCol, $clusterLevel), 'levelTimes', [999999]],
                [array_column_fixed($typeCol, $clusterLevel), 'moveTypeChangesPerLevel', []],
                [array_column_fixed($stdCol, $clusterLevel), 'knobStdDevs', []],
                [array_column_fixed($totalCol, $clusterLevel), 'knobTotalAmts', []],
                [$percentGoodMovesAll[$clusterLevel], 'percentGoodMovesAll', []],
            ];
            $sourceColumns = [];
            foreach ($allColumns as $col) {
                if (isset($_GET[$col[1]])) {
                    $sourceColumns[] = $col;
                }
            }
            if (count($sourceColumns) < 2) {
                $sourceColumns = $allColumns;
            }

            $pcaData = [];
            for ($i = 0; $i < count($sourceColumns); $i++) $pcaData[] = [];
            foreach (array_keys($sourceColumns[0][0]) as $i) {
                $good = true;
                for ($j = 0; $j < count($sourceColumns); $j++) {
                    $val = $sourceColumns[$j][0][$i];
                    if (!is_numeric($val) || in_array($val, $sourceColumns[$j][2])) {
                        $good = false;
                        break;
                    }
                }
                if ($good) {
                    for ($j = 0; $j < count($sourceColumns); $j++) {
                        $pcaData[$j][] = $sourceColumns[$j][0][$i];
                    }
                }
            }
            // scale to 0..1
            $pcaDataScaled = [];
            for ($i = 0; $i < count($pcaData); $i++) {
                $pcaDataScaled[] = [];
                $min_val = null;
                $max_val = null;
                for ($j = 0; $j < count($pcaData[$i]); $j++) {
                    $val = $pcaData[$i][$j];
                    if (is_null($min_val) || $val < $min_val) $min_val = $val;
                    if (is_null($max_val) || $val > $max_val) $max_val = $val;
                }
                $range = $max_val - $min_val;
                for ($j = 0; $j < count($pcaData[$i]); $j++) {
                    $pcaDataScaled[$i][] = ($pcaData[$i][$j] - $min_val) / $range;
                }
            }

            if (count($pcaDataScaled[0]) > 1) {

                $pca = new PCA\PCA($pcaDataScaled);
                $pca->changeDimension(2);
                $pca->applayingPca();
                $columns = $pca->getNewData();

                $bestDunn = 0;
                $bestColumn1 = 'pca1';
                $bestColumn2 = 'pca2';
                $bestSpace = null;
                $bestClusters = [];
                $clusterLevel = 0;

                for ($k = 2; $k < 5; $k++) {
                    $space = new KMeans\Space(2);
                    $xs = $columns[0];
                    $ys = $columns[1];
                    foreach ($xs as $xi => $x) {
                        $y = $ys[$xi];
                        $label = '';
                        foreach (array_column($pcaData, $xi) as $colIndex => $val) {
                            $label .= $sourceColumns[$colIndex][1] . ': ' . $val . "<br>";
                        }
                        $space->addPoint([$x, $y], $label);
                    }
                    $clusters = $space->solve($k);

                    $minInterDist = null;
                    $maxIntraDist = null;
                    for ($ci = 0; $ci < count($clusters); $ci++) {
                        for ($cj = $ci + 1; $cj < count($clusters); $cj++) {
                            // use distance between centers for simplicity
                            $interDist = sqrt
                                ( (pow(($clusters[$ci][0] - $clusters[$cj][0]), 2))
                                + (pow(($clusters[$ci][1] - $clusters[$cj][1]),  2))
                                );
                            if (is_null($minInterDist) || $interDist < $minInterDist) {
                                $minInterDist = $interDist;
                            }
                        }
                    }
                    for ($ci = 0; $ci < count($clusters); $ci++) {
                        $cluster = $clusters[$ci];
                        $intraDist = null;
                        // fudge intracluster distance by finding max distance from center to a point
                        foreach ($cluster as $point) {
                            $pointDist = sqrt
                                ( (pow(($point[0] - $cluster[0]), 2))
                                + (pow(($point[1] - $cluster[1]), 2))
                                );
                            if (is_null($intraDist) || $pointDist > $intraDist) {
                                $intraDist = $pointDist;
                            }
                        }
                        if (is_null($maxIntraDist) || $intraDist > $maxIntraDist) {
                            $maxIntraDist = $intraDist;
                        }
                    }

                    $thisDunn = $minInterDist / $maxIntraDist;
                    if ($thisDunn > $bestDunn) {
                        $bestDunn = $thisDunn;
                        $bestSpace = $space;
                        $bestClusters = $clusters;
                    }
                }

                $clusterPoints = [];
                foreach ($bestClusters as $cluster) {
                    $points = [];
                    foreach ($cluster->getIterator() as $point) {
                        $points[] = [$point[0], $point[1], $bestSpace[$point]];
                    }
                    $clusterPoints[] = $points;
                }
                $usedColumns = array_column($sourceColumns, 1);
                $eigenvectors = $pca->getEigenvectors();

            }
        }*/

    } else if (!isset($_GET['predictColumn'])) {
        $query =
        "SELECT session_id, level, event, event_custom, event_data_complex, client_time
        FROM log
        WHERE session_id=?
        ORDER BY client_time;";

        $params = array($reqSessionID);
        $stmt = queryMultiParam($db, $query, 's', $params);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
        $allEvents = array();
        while($stmt->fetch()) {
            $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom,
            'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
            // Group the variables into their sessionIDs in a big associative array
            $sessionAttributes[$session_id][] = $tuple;
            // Also make one big array of every event for easier extraction of unique attributes
            $allEvents[] = $tuple;
        }
        $stmt->close();

        // Sort every id's sessions by date
        foreach ($sessionAttributes as $i=>$val) {
            uasort($sessionAttributes[$i], function($a, $b) {
            return ($a['time'] <= $b['time']) ? -1 : 1;
            });
        }

        // Sort session ids by date, the default from before
        uasort($sessionAttributes, function($a, $b) {
        return ($a[0]['time'] <= $b[0]['time']) ? -1 : 1;
        });

        $sessions = array_keys($sessionAttributes);
        $uniqueSessions = array_unique($sessions);
        $numSessions = count($uniqueSessions);

        $numEvents = count($allEvents);
        $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE'; });
        $completeLevels = array_column($completeEvents, 'level');
        $levels = array_filter(array_unique(array_column($allEvents, 'level')), function($a) use($completeLevels) { return in_array($a, $completeLevels); });
        sort($levels);
        $numLevels = count($levels);

        $times = array(); // Construct array of each session's first time
        foreach ($sessionAttributes as $i=>$val) {
            $times[$i] = $val[0]['time'];
        }

        // Construct sessions and times array
        $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

        // Questions answered for session provided
        $questionsSingle = array();
        if (isset($reqSessionID) && !isset($reqLevel)) {
            $questionEvents = array();
            foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
                if ($val['event_custom'] === 3) {
                    $questionEvents []= $val;
                }
            }
            $numCorrect = 0;
            $numQuestions = count($questionEvents);
            for ($i = 0; $i < $numQuestions; $i++) {
                $jsonData = json_decode($questionEvents[$i]['event_data_complex'], true);
                if ($jsonData['answer'] === $jsonData['answered']) {
                    $numCorrect++;
                }
            }
            $questionsSingle = array('numCorrect'=>$numCorrect, 'numQuestions'=>$numQuestions);
        }

        // Graph data for one session
        $graphDataSingle = array();
        $basicInfoSingle = array();
        if (isset($reqSessionID)) {
            $graphEvents = null;
            $graphTimes = null;
            $graphEventData = null;
            $graphLevels = null;
            foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
                if (isset($reqLevel) && $val['level'] == $reqLevel) {
                    if ($val['event_custom'] === 1 ||
                        $val['event'] === 'SUCCEED'
                    ) {
                        if (!isset($graphEvents, $graphTimes, $graphEventData, $graphLevels)) {
                            $graphEvents = array();
                            $graphTimes = array();
                            $graphEventData = array();
                            $graphLevels = array();
                        }
                        $graphEvents []= $val['event'];
                        $graphTimes [] = $val['time'];
                        $graphEventData []= $val['event_data_complex'];
                    }
                }
            }
            $graphDataSingle = array('events'=>$graphEvents, 'times'=>$graphTimes, 'event_data'=>$graphEventData);

            // Basic info for one session
            $infoTimes = array();
            $infoEventData = array();
            $infoLevels = array();
            $infoEvents = array();
            foreach ($sessionAttributes[$reqSessionID] as $i=>$val) {
                $infoTimes []= $val['time'];
                $infoEventData []= $val['event_data_complex'];
                $infoLevels []= $val['level'];
                $infoEvents []= $val['event'];
            }
            $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels);
            $avgTime;
            $totalTime = 0;
            $numMovesPerChallenge;
            $totalMoves = 0;
            $avgMoves;
            $moveTypeChangesPerLevel;
            $moveTypeChangesTotal = 0;
            $moveTypeChangesAvg;
            $knobStdDevs;
            $knobNumStdDevs;
            $knobAmtsTotal = 0;
            $knobAmtsAvg;
            $knobSumTotal = 0;
            $knobSumAvg;
            $numLevelsThisSession = count(array_unique($dataObj['levels']));
            if (isset($dataObj['times'])) {
                // Basic features stuff
                $levelStartTime;
                $levelEndTime;
                $lastSlider = null;
                $startIndices = array();
                $endIndices = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $knobAmts = array();
                $numMovesPerChallenge = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $startIndices = array();
                $endIndices = array();
                $indicesToSplice = array();
                $levelTimes = array();
                $avgKnobStdDevs = array();
                $knobAvgs = array();
                foreach ($dataObj['levels'] as $i) {
                    $numMovesPerChallenge[$i] = array();
                    $indicesToSplice[$i] = array();

                    $startIndices[$i] = null;
                    $endIndices[$i] = null;
                    $moveTypeChangesPerLevel[$i] = 0;
                    $knobStdDevs[$i] = 0;
                    $knobNumStdDevs[$i] = 0;
                    $knobAmts[$i] = 0;
                    $knobAvgs[$i] = 0;
                    $avgKnobStdDevs[$i] = 0;
                }

                for ($i = 0; $i < count($dataObj['times']); $i++) {
                    if (!isset($endIndices[$dataObj['levels'][$i]])) {
                        $dataJson = json_decode($dataObj['data'][$i], true);
                        if ($dataObj['events'][$i] === 'BEGIN') {
                            if (!isset($startIndices[$dataObj['levels'][$i]])) { // check this space isn't filled by a previous attempt on the same level
                                $startIndices[$dataObj['levels'][$i]] = $i;
                            }
                        } else if ($dataObj['events'][$i] === 'COMPLETE') {
                            if (!isset($endIndices[$dataObj['levels'][$i]])) {
                                $endIndices[$dataObj['levels'][$i]] = $i;
                            }
                        } else if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE')) {
                            if ($lastSlider !== $dataJson['slider']) {
                                if (!isset($moveTypeChangesPerLevel[$dataObj['levels'][$i]])) $moveTypeChangesPerLevel[$dataObj['levels'][$i]] = 0;
                                $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                            }
                            $lastSlider = $dataJson['slider'];
                            $numMovesPerChallenge[$dataObj['levels'][$i]] []= $i;
                            //if (!isset($knobNumStdDevs[$dataObj['levels'][$i]])) $knobNumStdDevs[$dataObj['levels'][$i]] = 0;
                            $knobNumStdDevs[$dataObj['levels'][$i]]++;
                            //if (!isset($knobStdDevs[$dataObj['levels'][$i]])) $knobStdDevs[$dataObj['levels'][$i]] = 0;
                            $knobStdDevs[$dataObj['levels'][$i]] += $dataJson['stdev_val'];
                            //if (!isset($knobAmts[$dataObj['levels'][$i]])) $knobAmts[$dataObj['levels'][$i]] = 0;
                            $knobAmts[$dataObj['levels'][$i]] += ($dataJson['max_val']-$dataJson['min_val']);
                        }
                    }
                }

                foreach ($endIndices as $i=>$value) {
                    if (isset($endIndices[$i], $dataObj['times'][$endIndices[$i]], $dataObj['times'][$startIndices[$i]])) {
                        $levelStartTime = new DateTime($dataObj['times'][$startIndices[$i]]);
                        $levelEndTime = new DateTime($dataObj['times'][$endIndices[$i]]);
                        $levelTime = $levelEndTime->getTimestamp() - $levelStartTime->getTimestamp();
                        $totalTime += $levelTime;
                        $levelTimes[$i] = $levelTime;

                        $totalMoves += count($numMovesPerChallenge[$i]);
                        $moveTypeChangesTotal += $moveTypeChangesPerLevel[$i];

                        $knobAvgAmt = 0;
                        $knobAvgStdDev = 0;
                        if ($knobNumStdDevs[$i] != 0) {
                            $temp = $knobAmts[$i]/$knobNumStdDevs[$i];
                            $knobAmtsTotal += $temp;
                            $knobAvgAmt = $temp;
                            $knobAvgStdDev = ($knobStdDevs[$i]/$knobNumStdDevs[$i]);
                        }
                        $knobAvgs[$i] = $knobAvgAmt;
                        $avgKnobStdDevs[$i] = $knobAvgStdDev;

                        if ($knobAmts[$i] != 0) {
                            $knobSumTotal += $knobAmts[$i];
                        }
                    }
                }
                $avgTime = $totalTime / $numLevelsThisSession;
                $avgMoves = $totalMoves / $numLevelsThisSession;
                $moveTypeChangesAvg = $moveTypeChangesTotal / $numLevelsThisSession;
                $knobAmtsAvg = $knobAmtsTotal / $numLevelsThisSession;
                $knobSumAvg = $knobSumTotal / $numLevelsThisSession;
            }
            $numMoves = array();
            $filteredNumMoves = array_filter($numMovesPerChallenge, function ($value) { return isset($value) && !is_null($value); });
            foreach ($filteredNumMoves as $j=>$value) {
                $numMoves[$j] = count($numMovesPerChallenge[$j]);
            }
            /*
            * The above values are
            * levelTimes                -    array       - elements hold time per level for this session
            * avgTime                   -    value       - average value of levelTimes
            * totalTime                 -    value       - sum value of levelTimes
            *
            * numMovesPerChallenge      -    array       - elements hold number of moves per level (NOT a list of indices at this point)
            * totalMoves                -    value       - sum value of numMovesPerChallenge
            * avgMoves                  -    value       - average value of numMovesPerChallenge
            *
            * moveTypeChangesPerLevel   -    array       - elements hold number of times move type changed
            * moveTypeChangesTotal      -    value       - sum value of moveTypeChangesPerLevel
            * moveTypeChangesAvg        -    value       - average value of moveTypeChangesPerLevel
            *
            * knobStdDevs               -    array       - elements hold average std dev for moves in level
            * knobNumStdDevs            -    array       - elements hold number of std devs in level
            *
            * knobAvgs                  -    array       - elements hold average max-min for each level
            * knobAmtsTotalAvg          -    value       - sum value of knobAvgs
            * knobAmtsAvgAvg            -    value       - average value of knobAvgs
            *
            * knobTotalAmts             -    array       - elements hold total max-min for each level
            * knobTotalAvg              -    value       - average value of knobTotalAmts
            * knobSumTotal              -    value       - sum value of knobTotalAmts
            *
            * numMovesPerChallengeArray -    array[][]   - original numMovesPerChallenge (list of indices of moves per level)
            * dataObj                   -    object      - dataObj from old structure
            */
            $basicInfoSingle = array('levelTimes'=>$levelTimes, 'avgTime'=>$avgTime, 'totalTime'=>$totalTime, 'numMovesPerChallenge'=>$numMoves, 'totalMoves'=>$totalMoves, 'avgMoves'=>$avgMoves,
            'moveTypeChangesPerLevel'=>$moveTypeChangesPerLevel, 'moveTypeChangesTotal'=>$moveTypeChangesTotal, 'moveTypeChangesAvg'=>$moveTypeChangesAvg, 'knobStdDevs'=>$avgKnobStdDevs,
            'knobNumStdDevs'=>$knobNumStdDevs, 'knobAvgs'=>$knobAvgs, 'knobAmtsTotalAvg'=>$knobAmtsTotal, 'knobAmtsAvgAvg'=>$knobAmtsAvg, 'knobTotalAmts'=>$knobAmts, 'knobSumTotal'=>$knobSumTotal,
            'knobTotalAvg'=>$knobSumAvg, 'numMovesPerChallengeArray'=>$numMovesPerChallenge, 'dataObj'=>$dataObj);
        }

        // Get goals data for a single session or all sessions
        $goalsSingle = array();
        $percentGoodMovesAll = array();
        if (isset($reqSessionID, $reqLevel)) {
            $data = $basicInfoSingle;
            $dataObj = $data['dataObj'];
            $numMovesPerChallenge = $data['numMovesPerChallengeArray'][$reqLevel];

            $distanceToGoal1;
            $moveGoodness1;
            $absDistanceToGoal1;
            if (isset($numMovesPerChallenge)) {
                $absDistanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0);
                $distanceToGoal1 = array_fill(0, count($numMovesPerChallenge), 0); // this one is just -1/0/1
                $moveGoodness1 = array_fill(0, count($numMovesPerChallenge), 0); // an array of 0s
            }
            $moveNumbers = array();
            $cumulativeDistance1 = 0;

            foreach ($numMovesPerChallenge as $i=>$val) {
                $dataJson = json_decode($dataObj['data'][$i], true);
                if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE')) {
                    if ($dataJson['end_closeness'] < $dataJson['begin_closeness']) $moveGoodness1[$i] = 1;
                    else if ($dataJson['end_closeness'] > $dataJson['begin_closeness']) $moveGoodness1[$i] = -1;
                }
                $moveNumbers[$i] = $i;
                $cumulativeDistance1 += $moveGoodness1[$i];
                $distanceToGoal1[$i] = $cumulativeDistance1;
            }
            $goalSlope1 = 0;
            $deltaX = 0;
            $deltaY = 0;
            if (count($moveNumbers) > 0) {
                $deltaX = $moveNumbers[count($moveNumbers)-1] - $moveNumbers[0];
            }
            if (count($distanceToGoal1) > 0) {
                $deltaY = $distanceToGoal1[count($distanceToGoal1)-1] - $distanceToGoal1[0];
            }

            if ($deltaX != 0) {
                $goalSlope1 = $deltaY / $deltaX;
            }

            $distanceToGoal2;
            $moveGoodness2;
            $absDistanceToGoal2;
            if (isset($numMovesPerChallenge)) {
                $absDistanceToGoal2 = array_fill(0, count($numMovesPerChallenge), 0);
                $distanceToGoal2 = array_fill(0, count($numMovesPerChallenge), 0); // this one is just -1/0/1
                $moveGoodness2 = array_fill(0, count($numMovesPerChallenge), 0); // an array of 0s
            }
            $cumulativeDistance2 = 0;
            $lastCloseness2;

            $graph_min_x = -50;
            $graph_max_x =  50;
            $graph_max_y =  50;
            $graph_max_offset = $graph_max_x;
            $graph_max_wavelength = $graph_max_x*2;
            $graph_max_amplitude = $graph_max_y*(3/5);
            $graph_default_offset = ($graph_min_x+$graph_max_x)/2;
            $graph_default_wavelength = (2+($graph_max_x*2))/2;
            $graph_default_amplitude = $graph_max_y/4;
            $lastCloseness = array();
            $thisCloseness = array();
            $lastCloseness['OFFSET']['left'] = $lastCloseness['OFFSET']['right'] = $graph_max_offset-$graph_default_offset;
            $lastCloseness['AMPLITUDE']['left'] = $lastCloseness['AMPLITUDE']['right'] = $graph_max_amplitude-$graph_default_amplitude;
            $lastCloseness['WAVELENGTH']['left'] = $lastCloseness['WAVELENGTH']['right'] = $graph_max_wavelength-$graph_default_wavelength;

            foreach ($numMovesPerChallenge as $i=>$val) {
                $dataJson = json_decode($dataObj['data'][$i], true);
                if ($dataObj['events'][$i] === 'CUSTOM' && ($dataJson['event_custom'] === 'SLIDER_MOVE_RELEASE')) {
                    if ($dataJson['slider'] ===  'AMPLITUDE') {
                        $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_amplitude-$dataJson['end_val'];
                    } else if ($dataJson['slider'] === 'OFFSET') {
                        $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_offset-$dataJson['end_val'];
                    } else if ($dataJson['slider'] === 'WAVELENGTH') {
                        $thisCloseness[$dataJson['slider']][$dataJson['wave']] = $graph_max_wavelength-$dataJson['end_val'];
                    }
                    if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] < $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = 1;
                    else if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] > $lastCloseness[$dataJson['slider']][$dataJson['wave']]) $moveGoodness2[$i] = -1;

                    $lastCloseness[$dataJson['slider']][$dataJson['wave']] = $thisCloseness[$dataJson['slider']][$dataJson['wave']];
                    if ($thisCloseness[$dataJson['slider']][$dataJson['wave']] < 99999)
                        $absDistanceToGoal2[$i] = round($thisCloseness[$dataJson['slider']][$dataJson['wave']], 2);
                }
                $cumulativeDistance2 += $moveGoodness2[$i];
                $distanceToGoal2[$i] = $cumulativeDistance2;
            }

            $goalSlope2 = 0;
            $deltaY = 0;
            if (count($distanceToGoal2) > 0 ) {
                $deltaY = $distanceToGoal2[count($distanceToGoal2)-1] - $distanceToGoal2[0];
            }

            if ($deltaX != 0) {
                $goalSlope2 = $deltaY / $deltaX;
            }

            $goalsSingle = array('moveNumbers'=>$moveNumbers, 'distanceToGoal1'=>$distanceToGoal1, 'distanceToGoal2'=>$distanceToGoal2,
                'absDistanceToGoal1'=>$absDistanceToGoal1, 'absDistanceToGoal2'=>$absDistanceToGoal2, 'goalSlope1'=>$goalSlope1, 'goalSlope2'=>$goalSlope2, 'dataObj'=>$dataObj);
        }

        $totalNumSessions = getTotalNumSessions($_GET['gameID'], $db);

        $output = array('goalsSingle'=>$goalsSingle, 'sessionsAndTimes'=>$sessionsAndTimes, 'basicInfoSingle'=>$basicInfoSingle, 'graphDataSingle'=>$graphDataSingle,
        'questionsSingle'=>$questionsSingle, 'levels'=>$levels, 'numSessions'=>$numSessions, 'questionsTotal'=>$questionsTotal);

        // Return ALL the above information at once in a big array
        return replaceNans($output);
    } else {
        $predictColumn = $_GET['predictColumn'];
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $maxRows = $_GET['maxRows'];
        $minMoves = $_GET['minMoves'];
        $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);

        $tableLetter = 'b';
        $colLvl = intval(substr($predictColumn, 3));
        $params = array();
        $paramTypes = '';
        $query = 
"            SELECT
                a.session_id,
                a.level,
                a.event,
                a.event_custom,
                a.event_data_complex,
                a.client_time,
                a.app_id
            FROM
                log a
            WHERE a.session_id IN
            (
                SELECT session_id
                FROM log
                WHERE event_custom=1 AND app_id=?";
        foreach ($levelsForTable as $i=>$lvl) {
            if ($lvl >= $colLvl) break;
            $query .= " AND session_id IN
                (
                    SELECT session_id FROM log 
                    WHERE event='COMPLETE' AND app_id=? AND level=?";
            array_push($params, $gameID, $lvl);
            $paramTypes .= 'si';
        }
        $query .= "
            )
            GROUP BY session_id
            HAVING COUNT(*) >= ?
            )";
        $query .= "
            WHERE a.client_time BETWEEN ? AND ?
            ORDER BY client_time";
        array_push($params, $startDate, $endDate);
        $paramTypes .= 'ss';

        echo $query; return;

        $stmt = queryMultiParam($db, $query, $paramTypes, $params);
        if($stmt === NULL) {
            http_response_code(500);
            die('{ "errMessage": "Error running query." }');
        }
        if (!$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
            http_response_code(500);
            die('{ "errMessage": "Failed to bind to results." }');
        }
        $sessionAttributes = array(); // the master array of all sessions that will be built with attributes
        $allEvents = array();
        while($stmt->fetch()) {
            $tuple = array('session_id'=>$session_id, 'level'=>$level, 'event'=>$event, 'event_custom'=>$event_custom,
            'event_data_complex'=>$event_data_complex, 'time'=>$client_time);
            // Group the variables into their sessionIDs in a big associative array
            $sessionAttributes[$session_id][] = $tuple;
            // Also make one big array of every event for easier extraction of unique attributes
            $allEvents[] = $tuple;
        }
        $stmt->close();

        foreach ($sessionAttributes as $i=>$val) {
            uasort($sessionAttributes[$i], function($a, $b) {
                return ($a['time'] <= $b['time']) ? -1 : 1;
            });
        }

        // Sort session ids by date, the default from before
        uasort($sessionAttributes, function($a, $b) {
            return ($a[0]['time'] <= $b[0]['time']) ? -1 : 1;
        });

        $sessions = array_keys($sessionAttributes);
        $uniqueSessions = array_unique($sessions);
        $numSessions = count($uniqueSessions);

        $numEvents = count($allEvents);
        $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE'; });
        $completeLevels = array_column($completeEvents, 'level');
        $levels = array_filter(array_unique(array_column($allEvents, 'level')), function($a) use($completeLevels) { return in_array($a, $completeLevels); });
        sort($levels);
        $numLevels = count($levels);

        $times = array(); // Construct array of each session's first time
        foreach ($sessionAttributes as $i=>$val) {
            $times[$i] = $val[0]['time'];
        }

        return $numSessions;

        // Construct sessions and times array
        $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

        $columnData = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $predictColumn);

        $output = $columnData;

        // Return ALL the above information at once in a big array
        return replaceNans($output);
    }
}

$db->close();
?>
