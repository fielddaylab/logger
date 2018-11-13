<?php
// Indicate JSON data type

header('Content-Type: application/json');

$settings = json_decode(file_get_contents("config.json"), true);
ini_set('memory_limit', $settings['memory_limit']);
ini_set('max_execution_time', $settings['max_execution_time']);
ini_set('max_input_vars', $settings['max_input_vars']);
define('DATA_DIR', $settings['DATA_DIR']);
define('PYTHON_DIR', $settings['PYTHON_DIR']);
define('RSCRIPT_DIR', $settings['RSCRIPT_DIR']);

// Establish the database connection
include "database.php";

require_once "KMeans/Space.php";
require_once "KMeans/Point.php";
require_once "KMeans/Cluster.php";

require_once "PCA/pca.php";

date_default_timezone_set('America/Chicago');

$db = connectToDatabase($DB_NAME_DATA);
if ($db->connect_error) {
    http_response_code(500);
    die('{ "errMessage": "Failed to Connect to DB." }');
}

function average($arr) {
    if (!is_array($arr)) return $arr;
    $filtered = array_filter($arr, function($val) { return !is_string($val) && isset($val); });
    $total = array_sum($filtered);
    $length = count($filtered);
    return ($length > 0) ? $total / $length : 'NaN';
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

function sciToNum($sciStr) {
    if (is_numeric($sciStr)) return $sciStr + 0;
    else return $sciStr;
}

function predict($coefficients, $inputs, $isLinear = false) {
    if (!isset($coefficients) || !isset($inputs)) return null;
    if (count($coefficients) !== count($inputs)) return null;
    $linEq = 0;
    foreach ($coefficients as $i=>$coeff) {
        if ($i === 0) {
            $linEq += $coeff;
        } else {
            $linEq += $coeff * $inputs[$i-1];
        }
    }
    if ($isLinear) return $linEq;
    $exp = exp($linEq);
    return $exp / (1 + $exp);
}

if (isset($_GET['gameID'])) {
    //echo ''; return;
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
        print $output;
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

function array_sum2($arr) {
    if (is_array($arr)) return array_sum($arr);
    return $arr;
}

function analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column, $maxLevel = 100) {
    $sessionIDs = $sessionsAndTimes['sessions'];
    $sliderTypes = ['OFFSET', 'WAVELENGTH', 'AMPLITUDE'];
    $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);
    $shouldUseAvgs = false;
    if (isset($_GET['shouldUseAvgs'])) {
        $shouldUseAvgs = ($_GET['shouldUseAvgs'] === 'true');
    }
    $featuresToUse = null;
    if (isset($_GET['features'])) {
        $featuresToUse = $_GET['features'];
        foreach ($featuresToUse as $i=>$feature) {
            if ($feature === 'true') $featuresToUse[$i] = true;
            else $featuresToUse[$i] = false;
        }
    }

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
        if ($i > $maxLevel) break;
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
            if ($val['level'] > $maxLevel) break;
            $infoTimes[] = $val['time'];
            $infoEventData[] = $val['event_data_complex'];
            $infoLevels[] = $val['level'];
            $infoEvents[] = $val['event'];
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
        $numFailsPerLevel;
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
            $numMovesPerChallengePerSlider = array();
            $numFailsPerLevel = array();
            foreach ($dataObj['levels'] as $i) {
                $numMovesPerChallenge[$i] = array();
                $numMovesPerChallengePerSlider[$i] = array_fill_keys($sliderTypes, 0);
                $indicesToSplice[$i] = array();

                $startIndices[$i] = null;
                $endIndices[$i] = null;
                $moveTypeChangesPerLevel[$i] = 0;
                $knobStdDevs[$i] = 0;
                $knobNumStdDevs[$i] = 0;
                $knobAmts[$i] = 0;
                $knobAvgs[$i] = 0;
                $avgKnobStdDevs[$i] = 0;
                $numFailsPerLevel[$i] = 0;
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
                            $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                        }
                        $lastSlider = $dataJson['slider']; // possible slider values: "AMPLITUDE", "OFFSET", "WAVELENGTH"
                        $numMovesPerChallenge[$dataObj['levels'][$i]][] = $i;
                        if (!isset($numMovesPerChallengePerSlider[$dataObj['levels'][$i]][$lastSlider])) $numMovesPerChallengePerSlider[$dataObj['levels'][$i]][$lastSlider] = 0;
                        $numMovesPerChallengePerSlider[$dataObj['levels'][$i]][$lastSlider]++;
                        //if (!isset($knobNumStdDevs[$dataObj['levels'][$i]])) $knobNumStdDevs[$dataObj['levels'][$i]] = 0;
                        $knobNumStdDevs[$dataObj['levels'][$i]]++;
                        //if (!isset($knobStdDevs[$dataObj['levels'][$i]])) $knobStdDevs[$dataObj['levels'][$i]] = 0;
                        $knobStdDevs[$dataObj['levels'][$i]] += $dataJson['stdev_val'];
                        //if (!isset($knobAmts[$dataObj['levels'][$i]])) $knobAmts[$dataObj['levels'][$i]] = 0;
                        $knobAmts[$dataObj['levels'][$i]] += ($dataJson['max_val']-$dataJson['min_val']);
                    } else if ($dataObj['events'][$i] === 'FAIL') {
                        $numFailsPerLevel[$dataObj['levels'][$i]]++;
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
        $numMovesPerSliderCols = array();
        foreach ($sliderTypes as $i=>$slider) {
            $numMovesPerSliderCols[$slider] = array_column($numMovesPerChallengePerSlider, $slider);
        }
        $numFailsPerLevel = array_filter($numFailsPerLevel, function ($index) use ($endIndices) { return in_array($index, array_keys($endIndices)); }, ARRAY_FILTER_USE_KEY);
        $numMoves = array_filter($numMoves, function ($index) use ($endIndices) { return in_array($index, array_keys($endIndices)); }, ARRAY_FILTER_USE_KEY);

        $sessionData = array(
            'avgTime'=>$avgTime,
            'totalTime'=>$totalTime,
            'numMovesPerChallengeArray'=>$numMovesPerChallenge,
            'totalMoves'=>$totalMoves,
            'avgMoves'=>$avgMoves,
            'moveTypeChangesTotal'=>$moveTypeChangesTotal,
            'moveTypeChangesAvg'=>$moveTypeChangesAvg,
            'knobStdDevs'=>$avgKnobStdDevs,
            'knobAmtsTotalAvg'=>$knobAmtsTotal,
            'knobAmtsAvgAvg'=>$knobAmtsAvg,
            'knobSumTotal'=>$knobSumTotal,
            'knobTotalAvg'=>$knobSumAvg,
            'dataObj'=>$dataObj,
            'features'=>array()
        );

        // add/change features here
        $sessionData['features']['levelTimes'] = $levelTimes;
        $sessionData['features']['numMovesPerChallenge'] = $numMoves;
        $sessionData['features']['numLevels'] = count($levelTimes);
        $sessionData['features']['moveTypeChangesPerLevel'] = $moveTypeChangesPerLevel;
        $sessionData['features']['knobStdDevs'] = $avgKnobStdDevs;
        $sessionData['features']['knobTotalAmts'] = $knobAmts;
        $sessionData['features']['knobAvgs'] = $knobAvgs;
        foreach ($sliderTypes as $i=>$slider) {
            $sessionData['features'][$slider] = $numMovesPerSliderCols[$slider];
            $movesScalar = array_sum($numMoves);
            if ($movesScalar > 0) {
                $sessionData['features']['percent'.$slider] = array_sum($numMovesPerSliderCols[$slider]) / $movesScalar;
            } else {
                $sessionData['features']['percent'.$slider] = 0;
            }
        }
        $sessionData['features']['numFailsPerLevel'] = $numFailsPerLevel;

        $allData[$sessionID] = $sessionData;
    }
    
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
                $questionEvents[] = $jval;
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
        $numMovesAll[] = $numMoves;
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

    $percentGoodMovesAvgs = array();
    foreach ($sessionIDs as $index=>$session) {
        $data = $allData[$session];
        $dataObj = $data['dataObj'];
        $sessionLevels = array_keys($data['features']['numMovesPerChallenge']);

        $totalGoodMoves = 0;
        $totalMoves = 0;
        foreach ($sessionLevels as $j=>$level) {
            $numMovesPerChallenge = $data['numMovesPerChallengeArray'][$level];
            $numMoves = $data['features']['numMovesPerChallenge'][$level];

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
            $totalGoodMoves += $numGoodMoves;
            $totalMoves += $numMoves;
            if ($numMoves > 1) {
                $percentGoodMoves = $numGoodMoves / $numMoves;
            } else {
                $percentGoodMoves = 1; // if they only had 1 move or somehow beat the level with 0, give them 100% good moves
            }
            $percentGoodMovesAll[$level][$index] = $percentGoodMoves;
        }
        if ($totalMoves !== 0) {
            $percentGoodMovesAvgs[$index] = $totalGoodMoves / $totalMoves;
        } else {
            $percentGoodMovesAvgs[$index] = 1;
        }
    }

    // Add features that were just calculated above to every session
    foreach ($sessionIDs as $i=>$sessionID) {
        $allData[$sessionID]['features']['avgPercentGoodMoves'] = $percentGoodMovesAvgs[$i];
        foreach ($levelsForTable as $j=>$lvl) {
            if (!$featuresToUse['pgm_'.$lvl]) break;
            $allData[$sessionID]['features']['pgm_'.$lvl] = $percentGoodMovesAll[$lvl][$i];
        }
        $allData[$sessionID]['features']['percentQuestionsCorrect'] = ($questionsAll['numsQuestions'][$i] === 0) ? 0 : $questionsAll['numsCorrect'][$i] / $questionsAll['numsQuestions'][$i];
    }

    // Put features into columns
    $featureCols = array();
    $allFeatures = array_column($allData, 'features');
    if (isset($allFeatures[0])) {
        foreach ($allFeatures[0] as $i=>$featureCol) {
            $featureCols[$i] = array_column($allFeatures, $i);
        }
    }

    // All this stuff depends on the columns
    $newArray = array();
    $a = array_column($allEvents, 'level');
    $b = array_column($allEvents, 'event');

    foreach ($levels as $i) {
        $totalTimesPerLevelAll[$i] = average(array_column($featureCols['levelTimes'], $i));
        $totalMovesPerLevelArray[$i] = average(array_column($featureCols['numMovesPerChallenge'], $i));
        $totalMoveTypeChangesPerLevelAll[$i] = average(array_column($featureCols['moveTypeChangesPerLevel'], $i));
        $totalStdDevsPerLevelAll[$i] = average(array_column($featureCols['knobStdDevs'], $i));
        $totalKnobTotalsPerLevelAll[$i] = average(array_column($featureCols['knobTotalAmts'], $i));
        $totalKnobAvgsPerLevelAll[$i] = average(array_column($featureCols['knobAvgs'], $i));
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

    $basicInfoAll = array(
        'times'=>$totalTimesPerLevelAll,
        'numMoves'=>$totalMovesPerLevelArray,
        'moveTypeChanges'=>$totalMoveTypeChangesPerLevelAll,
        'knobStdDevs'=>$totalStdDevsPerLevelAll,
        'totalMaxMin'=>$totalKnobTotalsPerLevelAll,
        'avgMaxMin'=>$totalKnobAvgsPerLevelAll,
        'totalTime'=>$totalTimeAll,
        'totalMoves'=>$totalMovesAll,
        'totalMoveChanges'=>$totalMoveTypeChangesAll,
        'totalKnobTotals'=>$totalKnobTotalsAll,
        'totalKnobAvgs'=>$totalKnobAvgsAll,
        'avgTime'=>$avgTimeAll,
        'avgMoves'=>$avgMovesAll,
        'avgMoveChanges'=>$avgMoveTypeChangesAll,
        'avgKnobTotals'=>$avgKnobTotalsAll,
        'avgKnobAvgs'=>$avgKnobAvgsAll
    );

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

        // Cluster stuff
        $sourceColumns = [];
        $allColumns = [];
        $startLevel = 1;
        $endLevel = 8;
        for ($lvl = intval($startLevel); $lvl <= intval($endLevel); $lvl++) {
            $allColumns = array_merge($allColumns, [
                [array_column_fixed($featureCols['numMovesPerChallenge'], $lvl), 'numMovesPerChallenge', [216], $lvl],
                [array_column_fixed($featureCols['knobAvgs'], $lvl), 'knobAvgs', [], $lvl],
                [array_column_fixed($featureCols['levelTimes'], $lvl), 'levelTimes', [999999], $lvl],
                [array_column_fixed($featureCols['moveTypeChangesPerLevel'], $lvl), 'moveTypeChangesPerLevel', [], $lvl],
                [array_column_fixed($featureCols['knobStdDevs'], $lvl), 'knobStdDevs', [], $lvl],
                [array_column_fixed($featureCols['knobTotalAmts'], $lvl), 'knobTotalAmts', [], $lvl],
                [$percentGoodMovesAll[$lvl], 'percentGoodMovesAll', [], $lvl],
            ]);
        }
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
                if (isset($sourceColumns[$j][0][$i])) {
                    $val = $sourceColumns[$j][0][$i];
                    if (!is_numeric($val) || in_array($val, $sourceColumns[$j][2])) {
                        $good = false;
                        break;
                    }
                } else {
                    $good = false;
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
                if ($range > 0) {
                    $pcaDataScaled[$i][] = ($pcaData[$i][$j] - $min_val) / $range;
                } else {
                    // this is a hack because when the whole column is the same
                    // value it breaks PCA for some reason
                    $pcaDataScaled[$i][] = 0.5 + $j * 0.00001;
                }
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
            for ($k = 2; $k < 5; $k++) {
                $space = new KMeans\Space(2);
                $xs = $columns[0];
                $ys = $columns[1];
                foreach ($xs as $xi => $x) {
                    $y = $ys[$xi];
                    $labels = [];
                    foreach (array_column($pcaData, $xi) as $colIndex => $val) {
                        $prop = $sourceColumns[$colIndex][1];
                        $v = number_format($val, 3);
                        if (isset($labels[$prop])) {
                            $labels[$prop][] = $v;
                        } else {
                            $labels[$prop] = [$v];
                        }
                    }
                    $label = '';
                    foreach ($labels as $key => $vals) {
                        $label .= $key . ': [' . implode(',', $vals) . ']<br>';
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
            $usedColumns = [];
            foreach ($sourceColumns as $col) {
                $usedColumns[] = $col[3] . ' ' . $col[1];
            }
            $eigenvectors = $pca->getEigenvectors();
        }

        $numTypeChangesPerSession = array_map(function($session) { return array_sum($session); }, $featureCols['moveTypeChangesPerLevel']);

        return array('numLevelsAll'=>$numLevelsAll, 'numMovesAll'=>$numMovesAll, 'questionsAll'=>$questionsAll, 'questionAnswereds'=>$questionAnswereds, 'basicInfoAll'=>$basicInfoAll,
            'sessionsAndTimes'=>$sessionsAndTimes, 'levels'=>$levels, 'numSessions'=>count($sessionsAndTimes['sessions']), 'questionsTotal'=>$questionsTotal,
            'lvlsPercentComplete'=>$lvlsPercentComplete, 'numTypeChangesAll'=>$numTypeChangesPerSession, 'clusters'=>array('col1'=>$bestColumn1, 'col2'=>$bestColumn2, 'clusters'=>$clusterPoints, 'dunn'=>$bestDunn,
            'sourceColumns'=>$usedColumns, 'eigenvectors'=>$eigenvectors), 'predictors'=>null, 'predicted'=>null);
    }

    // filter features by what should be used for regressions
    foreach ($featureCols as $colName=>$feature) {
        if (isset($featuresToUse[$colName]) && !$featuresToUse[$colName]) {
            unset($featureCols[$colName]);
        }
    }
    // unset features that are calculated but never actually used
    if (isset($featureCols['knobTotalAmts'])) unset($featureCols['knobTotalAmts']);
    if (isset($featureCols['percentQuestionsCorrect'])) unset($featureCols['percentQuestionsCorrect']);

    // Linear regression stuff
    $regressionVars = array();
    $intercepts = array();
    $coefficients = array();
    $stdErrs = array();
    $significances = array();
    if (!isset($reqSessionID) && $_GET['table'] === 'binomialQuestionTable') {
        $predictors = array();
        $predicted = array();

        $quesIndex = intval(substr($column, 1, 1));
        $ansIndex = intval(substr($column, 2, 1));
        foreach ($questionAnswereds as $i=>$val) {
            if (isset($val[$quesIndex])) {
                $predictor = array();
                foreach ($featureCols as $j=>$feature) {
                    if ($shouldUseAvgs) {
                        $predictor[$j] = average($feature[$i]); 
                    } else {
                        $predictor[$j] = array_sum2($feature[$i]);
                    }
                }
                $predictors[] = $predictor;
                $predicted[] = ($val[$quesIndex] === $ansIndex) ? 1 : 0;
            }
        }
        foreach ($predictors as $i=>$predictor) {
            $predictors[$i][] = $predicted[$i];
        }
        $numTrue = count(array_filter($predicted, function ($a) { return $a === 1; }));
        $numFalse = count(array_filter($predicted, function ($a) { return $a === 0; }));
        return array('predictors'=>$predictors, 'predicted'=>$predicted, 'numSessions'=>array('numTrue'=>$numTrue, 'numFalse'=>$numFalse), 'featureNames'=>array_keys($featureCols));
    } else if ($_GET['table'] === 'individualLevelsTable') {
        $predictors = array();
        $predicted = array();

        foreach ($sessionIDs as $i=>$val) {
            $predictor = array();
            foreach ($featureCols as $j=>$feature) {
                if ($shouldUseAvgs) {
                    $predictor[$j] = average($feature[$i]); 
                } else {
                    $predictor[$j] = array_sum2($feature[$i]);
                }
            }
            $colLvl = intval(substr($column, 3));
            $predicted[] = (isset($levelsCompleteAll[$val][$colLvl]) && $levelsCompleteAll[$val][$colLvl]) ? 1 : 0;

            $predictors[] = $predictor;
        }
        foreach ($predictors as $i=>$predictor) {
            $predictors[$i][] = $predicted[$i];
        }
        $numTrue = count(array_filter($predicted, function ($a) { return $a === 1; }));
        $numFalse = count(array_filter($predicted, function ($a) { return $a === 0; }));
        return array('predictors'=>$predictors, 'predicted'=>$predicted, 'numSessions'=>array('numTrue'=>$numTrue, 'numFalse'=>$numFalse), 'featureNames'=>array_keys($featureCols));
    } else if ($_GET['table'] === 'numLevelsTable') {
        $predictors = array();
        $predicted = array();

        foreach ($sessionIDs as $i=>$val) {
            $predictor = array();
            foreach ($featureCols as $j=>$feature) {
                if ($shouldUseAvgs) {
                    $predictor[$j] = average($feature[$i]); 
                } else {
                    $predictor[$j] = array_sum2($feature[$i]);
                }
            }
            $predicted[] = $numLevelsAll[$i];

            $predictors[] = $predictor;
        }
        foreach ($predictors as $i=>$predictor) {
            $predictors[$i][] = $predicted[$i];
        }
        return array('predictors'=>$predictors, 'predicted'=>$predicted, 'numSessions'=>count($predictors), 'featureNames'=>array_keys($featureCols));
    } else if ($_GET['table'] === 'multinomialQuestionTable') {
        $predictors = array();
        $predicted = array();

        $quesIndex = intval(substr($column, 1, 1));
        foreach ($questionAnswereds as $i=>$val) {
            if (isset($val[$quesIndex])) {                
                $predictor = array();
                foreach ($featureCols as $j=>$feature) {
                    if ($shouldUseAvgs) {
                        $predictor[$j] = average($feature[$i]); 
                    } else {
                        $predictor[$j] = array_sum2($feature[$i]);
                    }
                }
                $predictors[] = $predictor;
                $predicted[] = $val[$quesIndex];
            }
        }
        foreach ($predictors as $i=>$predictor) {
            $predictors[$i][] = $predicted[$i];
        }
        return array('predictors'=>$predictors, 'predicted'=>$predicted, 'numSessions'=>count($predictors), 'featureNames'=>array_keys($featureCols));
    }
}

function random() {
    return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
}

function getAndParseData($column, $gameID, $db, $reqSessionID, $reqLevel) {
    $percentTesting = 0.5;
    $numMetrics = 2;
    $table = isset($_GET['table']) ? $_GET['table'] : null;
    if/* binomial/binary qs */ (!isset($reqSessionID) && ($table === 'basic' || (isset($column) && $table === 'binomialQuestionTable'))) {
        $minMoves = $_GET['minMoves'];
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
                    ) temp
                ) AS moves
            ) ";
            $params[] = $minMoves;
            //$params[] = $maxRows;
            $paramTypes .= 'i';
        }

        if (isset($column)) $minQuestions = 1;
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
            $params[] = $minQuestions;
            $params[] = $maxRows;
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

        if ($table === 'basic') {
            $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));
            $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
            $regression['totalNumSessions'] = getTotalNumSessions($gameID, $db);
            return $regression;
        }

        if (false) {
            // Construct sessions and times array
            $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));
            $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $column);
            $predictArray = $regression['predictors'];
            $predictedArray = $regression['predicted'];
            $numPredictors = $regression['numSessions'];

            $predictString = "# Generated " . date("Y-m-d H:i:s") . "\n" . $column . ",";
            $headerCols = $regression['featureNames'];
            $headerString = implode(',', $headerCols);//"num_slider_moves,num_type_changes,num_levels,total_time,avg_knob_max_min,avg_pgm,offset,wavelength,amp,num_fails";
            $predictString .= $headerString . ",result\n";
            foreach ($predictArray as $i=>$array) {
                $predictString .= $column . ',' . implode(',', $array) . "\n";
            }
            $numVariables = count(explode(',', $headerString)) + 1;

            if (!is_dir(DATA_DIR . '/questions')) {
                mkdir(DATA_DIR . '/questions', 0777, true);
            }

            $dataFile = DATA_DIR . '/questions/questionsDataForR_'. $_GET['column'] .'.txt';
            file_put_contents($dataFile, $predictString);
            unset($rResults);

            exec(RSCRIPT_DIR . " scripts/questionsScript.R " . $column . ' ' . str_replace(',', ' ', $headerString), $rResults);
            unset($sklRegOutput);
            unset($sklOutput);
            exec(PYTHON_DIR . " -W ignore scripts/sklearnscript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklOutput);
            exec(PYTHON_DIR . " -W ignore scripts/sklearnLogRegScript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklRegOutput);
    
            $algorithmNames = array();
            $accuracies = array();
            if ($sklOutput) {
                for ($i = 0, $lastRow = count($sklOutput); $i < $lastRow; $i++) {
                    $values = preg_split('/\ +/', $sklOutput[$i]);  // put "words" of this line into an array
                    $algorithmName = implode(' ', array_slice($values, 0, -$numMetrics));
                    $algorithmNames[] = $algorithmName;
                    $accuracies[$algorithmName] = array_slice($values, -$numMetrics);
                }
            }

            $accStart = 0;
            $coefficients = array();
            $stdErrs = array();
            $pValues = array();
            $coefStart = 0;
            foreach ($rResults as $key=>$string) {
                if (stristr($string, 'Accuracy')) {
                    $accStart = $key;
                }
                if (stristr($string, 'Estimate')) {
                    $coefStart = $key;
                    break;
                }
            }
            $accuracy = null;
            if (isset($rResults[$accStart+1])) {
                $accuracyLine = preg_split('/\ +/', $rResults[$accStart+1]);
                if (isset($accuracyLine[2])) $accuracy = $accuracyLine[2];
            }
            if ($coefStart !== 0) {
                for ($i = $coefStart+1, $lastRow = $numVariables+$coefStart; $i <= $lastRow; $i++) {
                    $values = preg_split('/\ +/', str_replace(['<', '>'], '', $rResults[$i]));  // put "words" of this line into an array
                    $coefficients[$values[0]] = sciToNum($values[1]);
                    $stdErrs[$values[0]] = sciToNum($values[2]);
                    $pValues[$values[0]] = sciToNum($values[4]);
                }
            }
            $percentCorrectR = $accuracy;

            $sklName = 'LogReg (SKL)';
            $algorithmNames[] = $sklName;
            $accuracies[$sklName] = (isset($sklRegOutput[0])) ? $sklRegOutput[0] : null;

            return array(
                'coefficients'=>$coefficients,
                'stdErrs'=>$stdErrs,
                'pValues'=>$pValues,
                'numSessions'=>$numPredictors,
                'percentCorrectR'=>$percentCorrectR,
                'algorithmNames'=>$algorithmNames,
                'accuracies'=>$accuracies
            );
        } else {
            $questionPredictCol = $column;
            $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));
            $returnArray = array();
            for ($predLevel = 1; $predLevel < 9; $predLevel++) { // repeat calculations for each cell, adding a level of data each iteration
                $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $questionPredictCol, $predLevel);
                $predictArray = $regression['predictors'];
                $predictedArray = $regression['predicted'];
                $numPredictors = $regression['numSessions'];

                $predictString = "# Generated " . date("Y-m-d H:i:s") . "\n" . $column . ",";
                $headerCols = $regression['featureNames'];
                $headerString = implode(',', $headerCols);//"num_slider_moves,num_type_changes,num_levels,total_time,avg_knob_max_min,avg_pgm,offset,wavelength,amp";
                $predictString .= $headerString . ",result\n";
                foreach ($predictArray as $i=>$array) {
                    $predictString .= $column . ',' . implode(',', $array) . "\n";
                }

                if (!is_dir(DATA_DIR . '/questionsPredict')) {
                    mkdir(DATA_DIR . '/questionsPredict', 0777, true);
                }

                $numVariables = count(explode(',', $headerString)) + 1;

                $dataFile = DATA_DIR . '/questionsPredict/questionsPredictDataForR_'. $questionPredictCol . '_' . $predLevel .'.txt';
                file_put_contents($dataFile, $predictString);
                unset($rResults);
                exec(RSCRIPT_DIR . " scripts/questionsPredictScript.R " . $column . ' ' . $predLevel . ' ' . str_replace(',', ' ', $headerString), $rResults);
                unset($sklOutput);
                unset($sklRegOutput);
                exec(PYTHON_DIR . " -W ignore scripts/sklearnscript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklOutput);
                exec(PYTHON_DIR . " -W ignore scripts/sklearnLogRegScript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklRegOutput);
        
                $algorithmNames = array();
                $accuracies = array();
                if ($sklOutput) {
                    for ($i = 0, $lastRow = count($sklOutput); $i < $lastRow; $i++) {
                        $values = preg_split('/\ +/', $sklOutput[$i]);  // put "words" of this line into an array
                        $algorithmName = implode(' ', array_slice($values, 0, -$numMetrics));
                        $algorithmNames[] = $algorithmName;
                        $accuracies[$algorithmName] = array_slice($values, -$numMetrics);
                    }
                }

                $sklName = 'LogReg (SKL)';
                $algorithmNames[] = $sklName;
                $accuracies[$sklName] = (isset($sklRegOutput[0])) ? array($sklRegOutput[0]) : array(null);

                $accStart = 0;
                $coefficients = array();
                $stdErrs = array();
                $pValues = array();
                $coefStart = 0;
                foreach ($rResults as $key=>$string) {
                    if (stristr($string, 'Accuracy')) {
                        $accStart = $key;
                    }
                    if (stristr($string, 'Estimate')) {
                        $coefStart = $key;
                        break; // estimate comes after accuracy in the output
                    }
                }
                $percentCorrectR = null;
                if (isset($rResults[$accStart+1])) {
                    $accuracyLine = preg_split('/\ +/', $rResults[$accStart+1]);
                    if (isset($accuracyLine[2])) $percentCorrectR = $accuracyLine[2];
                }
                if ($coefStart !== 0) {
                    for ($i = $coefStart+1, $lastRow = $numVariables+$coefStart; $i <= $lastRow; $i++) {
                        $values = preg_split('/\ +/', str_replace(['<', '>'], '', $rResults[$i]));  // put "words" of this line into an array
                        $coefficients[$values[0]] = sciToNum($values[1]);
                        $stdErrs[$values[0]] = sciToNum($values[2]);
                        $pValues[$values[0]] = sciToNum($values[4]);
                    }
                }
                $numTrue = $numPredictors['numTrue'];
                $numFalse = $numPredictors['numFalse'];
                $expectedAccuracy = number_format(max($numTrue, $numFalse) / ($numTrue + $numFalse), 2);

                $returnArray[$predLevel] = array(
                    'coefficients'=>$coefficients,
                    'stdErrs'=>$stdErrs,
                    'pValues'=>$pValues,
                    'numSessions'=>$numPredictors,
                    'numSessionsString'=>"$numTrue / $numFalse<br>($expectedAccuracy expected)",
                    'expectedAccuracy'=>$expectedAccuracy,
                    'percentCorrect'=>array_merge(array('Log reg'=>array($percentCorrectR)), $accuracies),
                    'algorithmNames'=>$algorithmNames
                );
            }
            return $returnArray;
        }
    } /* single session     */ else if (isset($reqSessionID)) {
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
                    $questionEvents[] = $val;
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
                        $graphEvents[] = $val['event'];
                        $graphTimes [] = $val['time'];
                        $graphEventData[] = $val['event_data_complex'];
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
                $infoTimes[] = $val['time'];
                $infoEventData[] = $val['event_data_complex'];
                $infoLevels[] = $val['level'];
                $infoEvents[] = $val['event'];
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
                            $numMovesPerChallenge[$dataObj['levels'][$i]][] = $i;
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

        $output = array(
            'goalsSingle'=>$goalsSingle,
            'sessionsAndTimes'=>$sessionsAndTimes,
            'basicInfoSingle'=>$basicInfoSingle,
            'graphDataSingle'=>$graphDataSingle,
            'questionsSingle'=>$questionsSingle,
            'levels'=>$levels,
            'numSessions'=>$numSessions
        );

        // Return ALL the above information at once in a big array
        return replaceNans($output);
    } /* level completion   */ else if (isset($column) && $table === 'individualLevelsTable') {
        $predictColumn = $column;
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];
        $maxRows = $_GET['maxRows'];
        $minMoves = $_GET['minMoves'];
        $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);
        $colLvl = intval(substr($predictColumn, 3));
        $lvlIndex = array_search($colLvl, $levelsForTable);
        $lvlsToUse = array_filter($levelsForTable, function ($a) use($colLvl) { return $a < $colLvl; });
        $isLvl1 = empty($lvlsToUse);

        $prevColLvl = $isLvl1 ? null : $levelsForTable[$lvlIndex-1];
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
            WHERE a.client_time BETWEEN ? AND ? AND a.session_id IN
            (
                SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN";
        array_push($params, $startDate, $endDate, $gameID);
        $paramTypes .= 'sss';
        if (!$isLvl1) {
            $query .= "
                    (
                        SELECT c.session_id FROM log c
                        WHERE c.app_id=? AND c.event='COMPLETE' AND c.level IN (" . implode(",", array_map('intval', $lvlsToUse)) . ") AND NOT EXISTS
                        (
                            SELECT * FROM log d WHERE level >= ? AND d.event='COMPLETE' AND d.session_id = c.session_id AND app_id=?
                        ) 
                        GROUP BY c.session_id
                        HAVING COUNT(DISTINCT c.level) = ?
                    )";

            array_push($params, $gameID, $colLvl, $gameID, count($lvlsToUse));
            $paramTypes .= 'sisi';

            $query .= "
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) a
            ) OR a.session_id IN
            (
                SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN
                    (
                        SELECT session_id FROM log WHERE app_id=? AND event='COMPLETE'
                        AND level IN (" . implode(",", array_map('intval', array_merge($lvlsToUse, [$colLvl]))) . ")
                        GROUP BY session_id
                        HAVING COUNT(DISTINCT level) = ?
                    )
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) b
            )
            ORDER BY a.client_time";
            array_push($params, $minMoves, $maxRows, $gameID, $gameID, count($lvlsToUse)+1, $minMoves, $maxRows);
            $paramTypes .= 'iissiii';
        } else {
            $query .= "
                    (
                        SELECT c.session_id FROM log c
                        WHERE c.app_id=? AND NOT EXISTS
                        (
                            SELECT * FROM log d WHERE level >= ? AND d.event='COMPLETE' AND d.session_id = c.session_id AND app_id=?
                        ) 
                    )";
            array_push($params, $gameID, $colLvl, $gameID);
            $paramTypes .= 'sis';

            $query .= "
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) a
            ) OR a.session_id IN
            (
            	SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN
                    (
                        SELECT session_id FROM log WHERE app_id=? AND event='COMPLETE' AND level=1
                        GROUP BY session_id
                        HAVING COUNT(DISTINCT level) = ?
                    )
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) b
            )
            ORDER BY a.client_time";
            array_push($params, $minMoves, $maxRows, $gameID, $gameID, count($lvlsToUse) + 1, $minMoves, $maxRows);
            $paramTypes .= 'iissiii';
        }

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

        $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $predictColumn);
        $predictArray = $regression['predictors'];
        $predictedArray = $regression['predicted'];
        $numPredictors = $regression['numSessions'];
        $headerCols = $regression['featureNames'];

        $predictString = "# Generated " . date("Y-m-d H:i:s") . "\n" . $predictColumn . ",";
        $headerString = implode(',', $headerCols);//"num_slider_moves,num_type_changes,total_time,avg_knob_max_min,offset,wavelength,amp";
        $predictString .= $headerString . ",result\n";
        foreach ($predictArray as $i=>$array) {
            $predictString .= $predictColumn . ',' . implode(',', $array) . "\n";
        }

        if (!is_dir(DATA_DIR . '/challenges')) {
            mkdir(DATA_DIR . '/challenges', 0777, true);
        }

        $dataFile = DATA_DIR . '/challenges/challengesDataForR_'. $colLvl .'.txt';
        file_put_contents($dataFile, $predictString);
        unset($rResults);
        $numVariables = count(explode(',', $headerString)) + 1;
        exec(RSCRIPT_DIR . " scripts/challengesScript.R " . $colLvl . ' ' . str_replace(',', ' ', $headerString), $rResults);
        unset($sklOutput);
        unset($sklRegOutput);
        exec(PYTHON_DIR . " -W ignore scripts/sklearnscript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklOutput);
        exec(PYTHON_DIR . " -W ignore scripts/sklearnLogRegScript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklRegOutput);

        $algorithmNames = array();
        $accuracies = array();
        if ($sklOutput) {
            for ($i = 0, $lastRow = count($sklOutput); $i < $lastRow; $i++) {
                $values = preg_split('/\ +/', $sklOutput[$i]);  // put "words" of this line into an array
                $algorithmName = implode(' ', array_slice($values, 0, -$numMetrics));
                $algorithmNames[] = $algorithmName;
                $accuracies[$algorithmName] = array_slice($values, -$numMetrics);
            }
        }

        $coefficients = array();
        $stdErrs = array();
        $pValues = array();
        $coefStart = 0;
        $accStart = 0;
        foreach ($rResults as $key=>$string) {
            if (stristr($string, 'Accuracy')) {
                $accStart = $key;
            }
            if (stristr($string, 'Estimate')) {
                $coefStart = $key;
                break;
            }
        }

        $percentCorrectR = null;
        if (isset($rResults[$accStart+1])) {
            $accuracyLine = preg_split('/\ +/', $rResults[$accStart+1]);
            if (isset($accuracyLine[2])) $percentCorrectR = $accuracyLine[2];
        }
        if ($coefStart !== 0) {
            for ($i = $coefStart+1, $lastRow = $numVariables+$coefStart; $i <= $lastRow; $i++) {
                $values = preg_split('/\ +/', str_replace(['<', '>'], '', $rResults[$i]));  // put "words" of this line into an array
                $coefficients[$values[0]] = sciToNum($values[1]);
                $stdErrs[$values[0]] = sciToNum($values[2]);
                $pValues[$values[0]] = sciToNum($values[4]);
            }
        }

        $sklName = 'LogReg (SKL)';
        $algorithmNames[] = $sklName;
        $accuracies[$sklName] = (isset($sklRegOutput[0])) ? $sklRegOutput[0] : null;

        $trueSessions = array_unique(array_column(array_filter($completeEvents, function ($a) use ($colLvl) { return $a['level'] == $colLvl; }), 'session_id'));
        $numTrue = count($trueSessions); // number of sessions who completed every level including current col
        $numFalse = $numSessions - $numTrue; // number of sessions who completed every level up to but not current col
        $expectedAccuracy = ($numTrue + $numFalse == 0) ? null : number_format(max($numTrue, $numFalse) / ($numTrue + $numFalse), 2);

        return array(
            'coefficients'=>$coefficients,
            'stdErrs'=>$stdErrs,
            'pValues'=>$pValues,
            'numSessions'=>array('numTrue'=>$numTrue, 'numFalse'=>$numFalse),
            'numSessionsString'=>"$numTrue / $numFalse<br>($expectedAccuracy expected)",
            'expectedAccuracy'=>$expectedAccuracy,
            'percentCorrect'=>array_merge(array('Log reg'=>array($percentCorrectR)), $accuracies),
            'algorithmNames'=>$algorithmNames,
        );
    } /* num levels         */ else if (isset($column) && $table === 'numLevelsTable') {
        $numLevelsColumn = $column;
        $minMoves = $_GET['minMoves'];
        $minQuestions = $_GET['minQuestions'];
        $startDate = $_GET['startDate'];
        $endDate = $_GET['endDate'];

        $levelsForTable = array(1, 3, 5, 7, 11, 13, 15, 19, 21, 23, 25, 27, 31, 33);
        $colLvl = intval(substr($numLevelsColumn, 3));
        $lvlIndex = array_search($colLvl, $levelsForTable);
        $maxRows = $_GET['maxRows'];
        $lvlsToUse = array_filter($levelsForTable, function ($a) use($colLvl) { return $a < $colLvl; });
        $isLvl1 = empty($lvlsToUse);

        $prevColLvl = $isLvl1 ? null : $levelsForTable[$lvlIndex-1];
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
            WHERE a.client_time BETWEEN ? AND ? AND a.session_id IN
            (
                SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN";
        array_push($params, $startDate, $endDate, $gameID);
        $paramTypes .= 'sss';
        if (!$isLvl1) {
            $query .= "
                    (
                        SELECT c.session_id FROM log c
                        WHERE c.app_id=? AND c.event='COMPLETE' AND c.level IN (" . implode(",", array_map('intval', $lvlsToUse)) . ") AND NOT EXISTS
                        (
                            SELECT * FROM log d WHERE level >= ? AND d.event='COMPLETE' AND d.session_id = c.session_id AND app_id=?
                        ) 
                        GROUP BY c.session_id
                        HAVING COUNT(DISTINCT c.level) = ?
                    )";

            array_push($params, $gameID, $colLvl, $gameID, count($lvlsToUse));
            $paramTypes .= 'sisi';

            $query .= "
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) a
            ) OR a.session_id IN
            (
                SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN
                    (
                        SELECT session_id FROM log WHERE app_id=? AND event='COMPLETE'
                        AND level IN (" . implode(",", array_map('intval', array_merge($lvlsToUse, [$colLvl]))) . ")
                        GROUP BY session_id
                        HAVING COUNT(DISTINCT level) = ?
                    )
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) b
            )
            ORDER BY a.client_time";
            array_push($params, $minMoves, $maxRows, $gameID, $gameID, count($lvlsToUse)+1, $minMoves, $maxRows);
            $paramTypes .= 'iissiii';
        } else {
            $query .= "
                    (
                        SELECT c.session_id FROM log c
                        WHERE c.app_id=? AND NOT EXISTS
                        (
                            SELECT * FROM log d WHERE level >= ? AND d.event='COMPLETE' AND d.session_id = c.session_id AND app_id=?
                        ) 
                    )";
            array_push($params, $gameID, $colLvl, $gameID);
            $paramTypes .= 'sis';

            $query .= "
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) a
            ) OR a.session_id IN
            (
            	SELECT * FROM 
                (
                    SELECT session_id
                    FROM log
                    WHERE event_custom=1 AND app_id=? AND session_id IN
                    (
                        SELECT session_id FROM log WHERE app_id=? AND event='COMPLETE' AND level=1
                        GROUP BY session_id
                        HAVING COUNT(DISTINCT level) = ?
                    )
                    GROUP BY session_id
                    HAVING COUNT(*) >= ?
                    LIMIT ?
                ) b
            )
            ORDER BY a.client_time";
            array_push($params, $minMoves, $maxRows, $gameID, $gameID, count($lvlsToUse) + 1, $minMoves, $maxRows);
            $paramTypes .= 'iissiii';
        }

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

        $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));

        $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $numLevelsColumn);
        $predictArray = $regression['predictors'];
        $predictedArray = $regression['predicted'];
        $numPredictors = $regression['numSessions'];

        $totalAvgPercentError = array();
        $totalAvgPercentErrorRand = array();
        for ($trial = 0; $trial < 10; $trial++) {
            $predictString = "# Generated " . date("Y-m-d H:i:s") . "\n" . $numLevelsColumn . ",";
            $headerCols = $regression['featureNames'];
            $headerString = implode(',', $headerCols);//"num_slider_moves,num_type_changes,total_time,avg_knob_max_min,offset,wavelength,amp";

            $predictString .= $headerString . ",result\n";
            $predict10Percent = array(); // Use 10% to test the model
            $predictString10Percent = '';
            foreach ($predictArray as $i=>$array) {
                if (random() < $percentTesting) {
                    $predictString .= $numLevelsColumn . ',' . implode(',', $array) . "\n";
                } else {
                    $predictString10Percent .= $numLevelsColumn . ',' . implode(',', $array) . "\n";
                    $predict10Percent[] = $i;
                }
            }
            $numVariables = count(explode(',', $headerString)) + 1;

            if (!is_dir(DATA_DIR . '/numLevels')) {
                mkdir(DATA_DIR . '/numLevels', 0777, true);
            }

            $dataFile = DATA_DIR . '/numLevels/numLevelsDataForR_'. $colLvl .'.txt';
            file_put_contents($dataFile, $predictString);
            unset($rResults);
            exec(RSCRIPT_DIR . " scripts/numLevelsScript.R " . $colLvl . ' ' . str_replace(',', ' ', $headerString), $rResults);
            file_put_contents($dataFile, $predictString10Percent, FILE_APPEND);
            $coefficients = array();
            $stdErrs = array();
            $pValues = array();
            $coefStart = 0;
            foreach ($rResults as $key=>$string) {
                if (stristr($string, 'Estimate')) {
                    $coefStart = $key;
                    break;
                }
            }

            if ($coefStart !== 0) {
                for ($i = $coefStart+1, $lastRow = $numVariables+$coefStart; $i <= $lastRow; $i++) {
                    $values = preg_split('/\ +/', str_replace(['<', '>'], '', $rResults[$i]));  // put "words" of this line into an array
                    $coefficients[$values[0]] = sciToNum($values[1]);
                    $stdErrs[$values[0]] = sciToNum($values[2]);
                    $pValues[$values[0]] = sciToNum($values[4]);
                }
                $numPredictions = count($predict10Percent);
                $numVariables = count($predictArray[0]);
                $totalPercentError = 0;
                $totalPercentErrorRand = 0;
                foreach ($predict10Percent as $i=>$index) {
                    $inputs = array_slice($predictArray[$index], 0, -1);
                    $actual = $predictArray[$index][0];
                    $prediction = round(predict($coefficients, $inputs, true));
                    // when actual=0, percent error can't be computed normally
                    $percentError = ($actual == 0 && $prediction == 0) ? 0 : 2 * ($actual - $prediction) / (abs($actual) + abs($prediction));
                    $totalPercentError += $percentError;

                    $predictionRand = array_rand($levelsForTable);
                    $percentErrorRand = ($actual == 0 && $predictionRand == 0) ? 0 : 2 * ($actual - $predictionRand) / (abs($actual) + abs($predictionRand));
                    $totalPercentErrorRand += $percentErrorRand;
                }
                $avgPercentError = ($numPredictions == 0) ? null : $totalPercentError / $numPredictions;
                $avgPercentErrorRand = ($numPredictions == 0) ? null : $totalPercentErrorRand / $numPredictions;
                if (isset($avgPercentError)) $totalAvgPercentError[] = $avgPercentError;
                if (isset($avgPercentErrorRand)) $totalAvgPercentErrorRand[] = $avgPercentErrorRand;
            }
        }
        $avgAvgPercentErrorRand = average($totalAvgPercentErrorRand);
        $avgAvgPercentError = average($totalAvgPercentError);
        if (is_numeric($avgAvgPercentError)) {
            $percentCorrectR = $avgAvgPercentError;
        } else {
            $percentCorrectR = null;
        }
        if (is_numeric($avgAvgPercentErrorRand)) {
            $percentCorrectRand = $avgAvgPercentErrorRand;
        } else {
            $percentCorrectRand = null;
        }
        $trueSessions = array_unique(array_column(array_filter($completeEvents, function ($a) use ($colLvl) { return $a['level'] == $colLvl; }), 'session_id'));
        $numTrue = count($trueSessions); // number of sessions who completed every level including current col
        $numFalse = $numSessions - $numTrue; // number of sessions who completed every level up to but not current col

        return array(
            'coefficients'=>$coefficients,
            'stdErrs'=>$stdErrs, 'pValues'=>$pValues,
            'numSessionsString'=>"$numTrue / $numFalse",
            'numSessions'=>array('numTrue'=>$numTrue, 'numFalse'=>$numFalse),
            'percentCorrect'=>array('Log reg'=>array($percentCorrectR), 'Random'=>array($percentCorrectRand))
        );
    } /* multinomial ques   */ else if (isset($column) && $table === 'multinomialQuestionTable') {
        $minMoves = $_GET['minMoves'];
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
                    ) temp
                ) AS moves
            ) ";
            $params[] = $minMoves;
            $paramTypes .= 'i';
        }
        if ($minQuestions == 0) $minQuestions = 1;
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
            $params[] = $minQuestions;
            $params[] = $maxRows;
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

        $multinomQuestionPredictCol = $column;
        $sessionsAndTimes = array('sessions'=>$uniqueSessions, 'times'=>array_values($times));
        $returnArray = array();
        for ($predLevel = 1; $predLevel < 9; $predLevel++) { // repeat calculations for each cell, adding a level of data each iteration
            $regression = analyze($levels, $allEvents, $sessionsAndTimes, $numLevels, $sessionAttributes, $multinomQuestionPredictCol, $predLevel);
            $predictArray = $regression['predictors'];
            $predictedArray = $regression['predicted'];
            $numPredictors = $regression['numSessions'];

            $predictString = "# Generated " . date("Y-m-d H:i:s") . "\n" . $multinomQuestionPredictCol . ",";
            $headerCols = $regression['featureNames'];
            $headerString = implode(',', $headerCols);//"num_slider_moves,num_type_changes,num_levels,total_time,avg_knob_max_min,avg_pgm,offset,wavelength,amp";
            $predictString .= $headerString . ",result\n";
            foreach ($predictArray as $i=>$array) {
                $predictString .= $multinomQuestionPredictCol . ',' . implode(',', $array) . "\n";
            }
            if (!is_dir(DATA_DIR . '/multinomQuestionsPredict')) {
                mkdir(DATA_DIR . '/multinomQuestionsPredict', 0777, true);
            }
            $numVariables = count(explode(',', $headerString)) + 1;
            $dataFile = DATA_DIR . '/multinomQuestionsPredict/multinomQuestionsPredictDataForR_'. $multinomQuestionPredictCol .'_'. $predLevel .'.txt';
            file_put_contents($dataFile, $predictString);
            unset($sklOutput);
            unset($sklRegOutput);
            exec(PYTHON_DIR . " -W ignore scripts/sklearnscript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklOutput);
            exec(PYTHON_DIR . " -W ignore scripts/sklearnLogRegScript.py $dataFile " . implode(' ', range(1, $numVariables)), $sklRegOutput);

            $algorithmNames = array();
            $accuracies = array();
            if ($sklOutput) {
                for ($i = 0, $lastRow = count($sklOutput); $i < $lastRow; $i++) {
                    $values = preg_split('/\ +/', $sklOutput[$i]);  // put "words" of this line into an array
                    $algorithmName = implode(' ', array_slice($values, 0, -$numMetrics));
                    $algorithmNames[] = $algorithmName;
                    $accuracies[$algorithmName] = array_slice($values, -$numMetrics);
                }
            }

            $sklName = 'LogReg (SKL)';
            $algorithmNames[] = $sklName;
            $accuracies[$sklName] = (isset($sklRegOutput[0])) ? array($sklRegOutput[0]) : array(null);

            $ansA = array_filter($predictedArray, function ($a) { return $a == 0; });
            $ansB = array_filter($predictedArray, function ($a) { return $a == 1; });
            $ansC = array_filter($predictedArray, function ($a) { return $a == 2; });
            $ansD = array_filter($predictedArray, function ($a) { return $a == 3; });
            $numA = count($ansA);
            $numB = count($ansB);
            $numC = count($ansC);
            $numD = count($ansD);
            $numSessions = array(
                'numA'=>$numA,
                'numB'=>$numB,
                'numC'=>$numC,
                'numD'=>$numD
            );
            $expectedAccuracy = number_format(max(...array_values($numSessions)) / array_sum($numSessions), 2);

            $returnArray[$predLevel] = array(
                'numSessions'=>$numSessions,
                'numSessionsString'=>"$numA / $numB / $numC / $numD<br>($expectedAccuracy expected)",
                'expectedAccuracy'=>$expectedAccuracy,
                'algorithmNames'=>$algorithmNames,
                'percentCorrect'=>$accuracies
            );
        }
        return $returnArray;
    } else {
        return array('error'=>'Incorrect parameters provided.');
    }
}

$db->close();
?>
