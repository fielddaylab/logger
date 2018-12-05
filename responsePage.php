<?php
// Indicate JSON data type
header('Content-Type: application/json');
$js_debug = false; // Whether the premade outputs should be returned (for quick responses in display debugging)

if ($js_debug && isset($_GET['table'])) {
    // premade output for numLevels
    if ($_GET['table'] == 'numLevels') {
        echo '{
            "coefficients": {
                "(Intercept)": 4.352,
                "levelTimes": 0.000001776,
                "numMovesPerChallenge": 0.004196,
                "moveTypeChangesPerLevel": 0.02516,
                "knobStdDevs": -0.02789,
                "knobAvgs": 0.03265,
                "percentOFFSET": -3.954,
                "percentWAVELENGTH": -3.65,
                "percentAMPLITUDE": "NA",
                "numFailsPerLevel": 0.002195,
                "pgm_1": 0.1792
            },
            "stdErrs": {
                "(Intercept)": 0.3704,
                "levelTimes": 0.000006987,
                "numMovesPerChallenge": 0.001458,
                "moveTypeChangesPerLevel": 0.003262,
                "knobStdDevs": 0.01009,
                "knobAvgs": 0.003438,
                "percentOFFSET": 0.5014,
                "percentWAVELENGTH": 0.5453,
                "percentAMPLITUDE": "NA",
                "numFailsPerLevel": 0.002492,
                "pgm_1": 0.0943
            },
            "pValues": {
                "(Intercept)": 2e-16,
                "levelTimes": 0.79938,
                "numMovesPerChallenge": 0.00405,
                "moveTypeChangesPerLevel": 2.31e-14,
                "knobStdDevs": 0.0058,
                "knobAvgs": 2e-16,
                "percentOFFSET": 6.15e-15,
                "percentWAVELENGTH": 3.14e-11,
                "percentAMPLITUDE": "NA",
                "numFailsPerLevel": 0.37863,
                "pgm_1": 0.05754
            },
            "numSessionsString": "2299 / 673",
            "numSessions": {
                "numTrue": 2299,
                "numFalse": 673
            },
            "percentCorrect": {
                "Log reg": [
                    2
                ],
                "Random": [
                    0.08020665522210536
                ]
            }
        }'; exit;
    }

    // premade output for levelCompletion
    if ($_GET['table'] == 'levelCompletion') {
        echo '{
            "coefficients": {
                "(Intercept)": -5.6851392,
                "levelTimes": 0.0010589,
                "numMovesPerChallenge": 0.0420601,
                "moveTypeChangesPerLevel": 0.0237183,
                "knobStdDevs": -0.2304107,
                "knobAvgs": 0.1134087,
                "percentOFFSET": 2.1984986,
                "percentWAVELENGTH": 0.9524041,
                "percentAMPLITUDE": 1.1569211,
                "numFailsPerLevel": 0.3526971
            },
            "stdErrs": {
                "(Intercept)": 2.3184015,
                "levelTimes": 0.0003791,
                "numMovesPerChallenge": 0.0109026,
                "moveTypeChangesPerLevel": 0.018985,
                "knobStdDevs": 0.02766,
                "knobAvgs": 0.0103083,
                "percentOFFSET": 2.3382785,
                "percentWAVELENGTH": 2.3479807,
                "percentAMPLITUDE": 2.3442188,
                "numFailsPerLevel": 0.0257545
            },
            "pValues": {
                "(Intercept)": 0.014199,
                "levelTimes": 0.00522,
                "numMovesPerChallenge": 0.000114,
                "moveTypeChangesPerLevel": 0.211549,
                "knobStdDevs": 2e-16,
                "knobAvgs": 2e-16,
                "percentOFFSET": 0.347104,
                "percentWAVELENGTH": 0.685017,
                "percentAMPLITUDE": 0.621645,
                "numFailsPerLevel": 2e-16
            },
            "numSessions": {
                "numTrue": 2982,
                "numFalse": 922
            },
            "numSessionsString": "2982 / 922<br>(0.76 expected)",
            "expectedAccuracy": "0.76",
            "percentCorrect": {
                "Log reg": [
                    "0.9461066"
                ],
                "Nearest Neighbors": [
                    "0.9211065573770492",
                    "0.9210500257026506"
                ],
                "Linear SVM": [
                    "0.9241803278688525",
                    "0.9243410986490763"
                ],
                "RBF SVM": [
                    "0.9221311475409836",
                    "0.9214358243781239"
                ],
                "Gaussian Process": [
                    "0.9451844262295082",
                    "0.9456532553177046"
                ],
                "Decision Tree": [
                    "0.961577868852459",
                    "0.9615641265758652"
                ],
                "Random Forest": [
                    "0.9574795081967213",
                    "0.9575248212067115"
                ],
                "Neural Net": [
                    "0.9410860655737705",
                    "0.941393604107437"
                ],
                "AdaBoost": [
                    "0.9661885245901639",
                    "0.9660906117775842"
                ],
                "Naive Bayes": [
                    "0.8611680327868853",
                    "0.8690106357279277"
                ],
                "QDA": [
                    "0.7663934426229508",
                    "0.6758798330698496"
                ],
                "LogReg (SKL)": [
                    "0.9400614754098361"
                ]
            },
            "algorithmNames": [
                "Nearest Neighbors",
                "Linear SVM",
                "RBF SVM",
                "Gaussian Process",
                "Decision Tree",
                "Random Forest",
                "Neural Net",
                "AdaBoost",
                "Naive Bayes",
                "QDA",
                "LogReg (SKL)"
            ]
        }'; exit;
    }
    
    // premade output for binomialQuestion
    if ($_GET['table'] == 'binomialQuestion') {
        echo '{
            "1": {
                "coefficients": {
                    "(Intercept)": -0.7357,
                    "levelTimes": -0.00003334,
                    "numMovesPerChallenge": 0.009454,
                    "numLevels": 0.3162,
                    "moveTypeChangesPerLevel": -0.03334,
                    "knobStdDevs": 0.07731,
                    "knobAvgs": -0.02407,
                    "percentOFFSET": 0.6356,
                    "percentWAVELENGTH": 0.2555,
                    "percentAMPLITUDE": 0.9611,
                    "numFailsPerLevel": -0.03061,
                    "avgPercentGoodMoves": 0.7373
                },
                "stdErrs": {
                    "(Intercept)": 0.9946,
                    "levelTimes": 0.00005218,
                    "numMovesPerChallenge": 0.009922,
                    "numLevels": 0.2449,
                    "moveTypeChangesPerLevel": 0.02541,
                    "knobStdDevs": 0.03051,
                    "knobAvgs": 0.01121,
                    "percentOFFSET": 1.027,
                    "percentWAVELENGTH": 1.044,
                    "percentAMPLITUDE": 1.125,
                    "numFailsPerLevel": 0.0215,
                    "avgPercentGoodMoves": 0.9886
                },
                "pValues": {
                    "(Intercept)": 0.4595,
                    "levelTimes": 0.5228,
                    "numMovesPerChallenge": 0.3407,
                    "numLevels": 0.1967,
                    "moveTypeChangesPerLevel": 0.1895,
                    "knobStdDevs": 0.0113,
                    "knobAvgs": 0.0318,
                    "percentOFFSET": 0.5361,
                    "percentWAVELENGTH": 0.8067,
                    "percentAMPLITUDE": 0.3928,
                    "numFailsPerLevel": 0.1545,
                    "avgPercentGoodMoves": 0.4558
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5095238"
                    ],
                    "Nearest Neighbors": [
                        "0.5142857142857142",
                        "0.5114403937222292"
                    ],
                    "Linear SVM": [
                        "0.5006802721088436",
                        "0.45724079186899075"
                    ],
                    "RBF SVM": [
                        "0.5061224489795918",
                        "0.5048709855842932"
                    ],
                    "Gaussian Process": [
                        "0.5047619047619047",
                        "0.45553359884068545"
                    ],
                    "Decision Tree": [
                        "0.4748299319727891",
                        "0.4564564421294737"
                    ],
                    "Random Forest": [
                        "0.508843537414966",
                        "0.4935954356046848"
                    ],
                    "Neural Net": [
                        "0.5170068027210885",
                        "0.49727316151891554"
                    ],
                    "AdaBoost": [
                        "0.507482993197279",
                        "0.5068800632788728"
                    ],
                    "Naive Bayes": [
                        "0.5115646258503401",
                        "0.35623681725704914"
                    ],
                    "QDA": [
                        "0.5129251700680272",
                        "0.38047134523324994"
                    ],
                    "LogReg (SKL)": [
                        "0.49795918367346936"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "2": {
                "coefficients": {
                    "(Intercept)": -1.088,
                    "levelTimes": -0.00004761,
                    "numMovesPerChallenge": 0.003937,
                    "numLevels": 0.1956,
                    "moveTypeChangesPerLevel": -0.02184,
                    "knobStdDevs": 0.05982,
                    "knobAvgs": -0.01793,
                    "percentOFFSET": 1.028,
                    "percentWAVELENGTH": 0.9165,
                    "percentAMPLITUDE": 0.893,
                    "numFailsPerLevel": -0.0147,
                    "avgPercentGoodMoves": 1.064
                },
                "stdErrs": {
                    "(Intercept)": 0.9233,
                    "levelTimes": 0.00007085,
                    "numMovesPerChallenge": 0.005354,
                    "numLevels": 0.1947,
                    "moveTypeChangesPerLevel": 0.0135,
                    "knobStdDevs": 0.02194,
                    "knobAvgs": 0.007714,
                    "percentOFFSET": 1.019,
                    "percentWAVELENGTH": 1.025,
                    "percentAMPLITUDE": 1.117,
                    "numFailsPerLevel": 0.008849,
                    "avgPercentGoodMoves": 0.917
                },
                "pValues": {
                    "(Intercept)": 0.2387,
                    "levelTimes": 0.5016,
                    "numMovesPerChallenge": 0.4622,
                    "numLevels": 0.3149,
                    "moveTypeChangesPerLevel": 0.1057,
                    "knobStdDevs": 0.0064,
                    "knobAvgs": 0.0201,
                    "percentOFFSET": 0.3134,
                    "percentWAVELENGTH": 0.3714,
                    "percentAMPLITUDE": 0.4239,
                    "numFailsPerLevel": 0.0966,
                    "avgPercentGoodMoves": 0.246
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5165986"
                    ],
                    "Nearest Neighbors": [
                        "0.4897959183673469",
                        "0.48778778162460157"
                    ],
                    "Linear SVM": [
                        "0.5020408163265306",
                        "0.4564048758306892"
                    ],
                    "RBF SVM": [
                        "0.5115646258503401",
                        "0.5111027484279764"
                    ],
                    "Gaussian Process": [
                        "0.5047619047619047",
                        "0.5017552362670884"
                    ],
                    "Decision Tree": [
                        "0.5129251700680272",
                        "0.490967529815804"
                    ],
                    "Random Forest": [
                        "0.5210884353741496",
                        "0.5130575424591222"
                    ],
                    "Neural Net": [
                        "0.5142857142857142",
                        "0.5024251686125883"
                    ],
                    "AdaBoost": [
                        "0.5006802721088436",
                        "0.4996790833297812"
                    ],
                    "Naive Bayes": [
                        "0.5102040816326531",
                        "0.36009068524556476"
                    ],
                    "QDA": [
                        "0.5061224489795918",
                        "0.3666698015611775"
                    ],
                    "LogReg (SKL)": [
                        "0.5238095238095238"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "3": {
                "coefficients": {
                    "(Intercept)": -1.567,
                    "levelTimes": 0.00001354,
                    "numMovesPerChallenge": 0.005197,
                    "numLevels": 0.1494,
                    "moveTypeChangesPerLevel": -0.03397,
                    "knobStdDevs": 0.0603,
                    "knobAvgs": -0.01858,
                    "percentOFFSET": 2.161,
                    "percentWAVELENGTH": 0.948,
                    "percentAMPLITUDE": 1.363,
                    "numFailsPerLevel": -0.003236,
                    "avgPercentGoodMoves": 1.547
                },
                "stdErrs": {
                    "(Intercept)": 0.9813,
                    "levelTimes": 0.00001629,
                    "numMovesPerChallenge": 0.00313,
                    "numLevels": 0.2202,
                    "moveTypeChangesPerLevel": 0.009626,
                    "knobStdDevs": 0.02006,
                    "knobAvgs": 0.00699,
                    "percentOFFSET": 1.204,
                    "percentWAVELENGTH": 1.22,
                    "percentAMPLITUDE": 1.282,
                    "numFailsPerLevel": 0.006642,
                    "avgPercentGoodMoves": 0.9744
                },
                "pValues": {
                    "(Intercept)": 0.110394,
                    "levelTimes": 0.406014,
                    "numMovesPerChallenge": 0.096765,
                    "numLevels": 0.497451,
                    "moveTypeChangesPerLevel": 0.000418,
                    "knobStdDevs": 0.002652,
                    "knobAvgs": 0.007847,
                    "percentOFFSET": 0.072652,
                    "percentWAVELENGTH": 0.437174,
                    "percentAMPLITUDE": 0.287549,
                    "numFailsPerLevel": 0.626105,
                    "avgPercentGoodMoves": 0.112384
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5338776"
                    ],
                    "Nearest Neighbors": [
                        "0.5319727891156463",
                        "0.5306550041669089"
                    ],
                    "Linear SVM": [
                        "0.5047619047619047",
                        "0.45843336178552624"
                    ],
                    "RBF SVM": [
                        "0.5197278911564626",
                        "0.5187648948648849"
                    ],
                    "Gaussian Process": [
                        "0.5020408163265306",
                        "0.4750300025947325"
                    ],
                    "Decision Tree": [
                        "0.5020408163265306",
                        "0.5013294460641399"
                    ],
                    "Random Forest": [
                        "0.5034013605442177",
                        "0.4889681396850528"
                    ],
                    "Neural Net": [
                        "0.5360544217687074",
                        "0.5316507079897003"
                    ],
                    "AdaBoost": [
                        "0.5102040816326531",
                        "0.5102040816326531"
                    ],
                    "Naive Bayes": [
                        "0.5034013605442177",
                        "0.4781447134450351"
                    ],
                    "QDA": [
                        "0.5183673469387755",
                        "0.5138116052391105"
                    ],
                    "LogReg (SKL)": [
                        "0.49795918367346936"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "4": {
                "coefficients": {
                    "(Intercept)": -0.4557,
                    "levelTimes": 0.00001146,
                    "numMovesPerChallenge": 0.00299,
                    "numLevels": 0.08883,
                    "moveTypeChangesPerLevel": -0.01725,
                    "knobStdDevs": 0.05537,
                    "knobAvgs": -0.01809,
                    "percentOFFSET": 1.355,
                    "percentWAVELENGTH": 0.1352,
                    "percentAMPLITUDE": 0.3897,
                    "numFailsPerLevel": -0.004748,
                    "avgPercentGoodMoves": 0.4499
                },
                "stdErrs": {
                    "(Intercept)": 0.9252,
                    "levelTimes": 0.00001609,
                    "numMovesPerChallenge": 0.002046,
                    "numLevels": 0.2261,
                    "moveTypeChangesPerLevel": 0.006572,
                    "knobStdDevs": 0.01907,
                    "knobAvgs": 0.006645,
                    "percentOFFSET": 1.334,
                    "percentWAVELENGTH": 1.324,
                    "percentAMPLITUDE": 1.416,
                    "numFailsPerLevel": 0.003815,
                    "avgPercentGoodMoves": 0.9175
                },
                "pValues": {
                    "(Intercept)": 0.62235,
                    "levelTimes": 0.47652,
                    "numMovesPerChallenge": 0.14393,
                    "numLevels": 0.69439,
                    "moveTypeChangesPerLevel": 0.00868,
                    "knobStdDevs": 0.00369,
                    "knobAvgs": 0.00648,
                    "percentOFFSET": 0.3097,
                    "percentWAVELENGTH": 0.91872,
                    "percentAMPLITUDE": 0.7832,
                    "numFailsPerLevel": 0.21326,
                    "avgPercentGoodMoves": 0.62389
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5280272"
                    ],
                    "Nearest Neighbors": [
                        "0.5224489795918368",
                        "0.5218651169076902"
                    ],
                    "Linear SVM": [
                        "0.5061224489795918",
                        "0.4640539946628525"
                    ],
                    "RBF SVM": [
                        "0.5224489795918368",
                        "0.522149818172025"
                    ],
                    "Gaussian Process": [
                        "0.4816326530612245",
                        "0.48039278122596557"
                    ],
                    "Decision Tree": [
                        "0.527891156462585",
                        "0.441941959809515"
                    ],
                    "Random Forest": [
                        "0.5265306122448979",
                        "0.5195872264568479"
                    ],
                    "Neural Net": [
                        "0.5183673469387755",
                        "0.5113042476026554"
                    ],
                    "AdaBoost": [
                        "0.508843537414966",
                        "0.50837908685933"
                    ],
                    "Naive Bayes": [
                        "0.5020408163265306",
                        "0.4757114963781765"
                    ],
                    "QDA": [
                        "0.5183673469387755",
                        "0.5183940936097216"
                    ],
                    "LogReg (SKL)": [
                        "0.5401360544217687"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "5": {
                "coefficients": {
                    "(Intercept)": 0.01508,
                    "levelTimes": 0.000002574,
                    "numMovesPerChallenge": 0.002373,
                    "numLevels": 0.1606,
                    "moveTypeChangesPerLevel": -0.0149,
                    "knobStdDevs": 0.05126,
                    "knobAvgs": -0.01759,
                    "percentOFFSET": 0.5761,
                    "percentWAVELENGTH": -0.4081,
                    "percentAMPLITUDE": -0.3393,
                    "numFailsPerLevel": -0.004205,
                    "avgPercentGoodMoves": -0.03457
                },
                "stdErrs": {
                    "(Intercept)": 0.9265,
                    "levelTimes": 0.00001301,
                    "numMovesPerChallenge": 0.001738,
                    "numLevels": 0.2549,
                    "moveTypeChangesPerLevel": 0.005333,
                    "knobStdDevs": 0.01804,
                    "knobAvgs": 0.006293,
                    "percentOFFSET": 1.664,
                    "percentWAVELENGTH": 1.646,
                    "percentAMPLITUDE": 1.732,
                    "numFailsPerLevel": 0.003729,
                    "avgPercentGoodMoves": 0.919
                },
                "pValues": {
                    "(Intercept)": 0.98701,
                    "levelTimes": 0.84314,
                    "numMovesPerChallenge": 0.17214,
                    "numLevels": 0.52849,
                    "moveTypeChangesPerLevel": 0.00521,
                    "knobStdDevs": 0.00449,
                    "knobAvgs": 0.0052,
                    "percentOFFSET": 0.72919,
                    "percentWAVELENGTH": 0.80415,
                    "percentAMPLITUDE": 0.84467,
                    "numFailsPerLevel": 0.25944,
                    "avgPercentGoodMoves": 0.96999
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5367347"
                    ],
                    "Nearest Neighbors": [
                        "0.5142857142857142",
                        "0.5132520516608859"
                    ],
                    "Linear SVM": [
                        "0.5061224489795918",
                        "0.47240195082127334"
                    ],
                    "RBF SVM": [
                        "0.508843537414966",
                        "0.5087161592743429"
                    ],
                    "Gaussian Process": [
                        "0.5142857142857142",
                        "0.49556262294160236"
                    ],
                    "Decision Tree": [
                        "0.5156462585034014",
                        "0.5133840360136188"
                    ],
                    "Random Forest": [
                        "0.4925170068027211",
                        "0.4896426817738082"
                    ],
                    "Neural Net": [
                        "0.5156462585034014",
                        "0.5060534978190359"
                    ],
                    "AdaBoost": [
                        "0.5292517006802722",
                        "0.5291522954483574"
                    ],
                    "Naive Bayes": [
                        "0.5020408163265306",
                        "0.4736366646262353"
                    ],
                    "QDA": [
                        "0.5061224489795918",
                        "0.44021455282601357"
                    ],
                    "LogReg (SKL)": [
                        "0.527891156462585"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "6": {
                "coefficients": {
                    "(Intercept)": 0.1264,
                    "levelTimes": 0.000002608,
                    "numMovesPerChallenge": 0.002254,
                    "numLevels": -0.19,
                    "moveTypeChangesPerLevel": -0.01328,
                    "knobStdDevs": 0.0447,
                    "knobAvgs": -0.01506,
                    "percentOFFSET": 2.213,
                    "percentWAVELENGTH": 1.495,
                    "percentAMPLITUDE": 1.689,
                    "numFailsPerLevel": -0.003861,
                    "avgPercentGoodMoves": -0.1507
                },
                "stdErrs": {
                    "(Intercept)": 0.9378,
                    "levelTimes": 0.00001292,
                    "numMovesPerChallenge": 0.001634,
                    "numLevels": 0.3048,
                    "moveTypeChangesPerLevel": 0.00474,
                    "knobStdDevs": 0.01665,
                    "knobAvgs": 0.005775,
                    "percentOFFSET": 2.132,
                    "percentWAVELENGTH": 2.117,
                    "percentAMPLITUDE": 2.193,
                    "numFailsPerLevel": 0.003616,
                    "avgPercentGoodMoves": 0.9302
                },
                "pValues": {
                    "(Intercept)": 0.89277,
                    "levelTimes": 0.84005,
                    "numMovesPerChallenge": 0.16763,
                    "numLevels": 0.53314,
                    "moveTypeChangesPerLevel": 0.00508,
                    "knobStdDevs": 0.00724,
                    "knobAvgs": 0.00911,
                    "percentOFFSET": 0.2994,
                    "percentWAVELENGTH": 0.47992,
                    "percentAMPLITUDE": 0.44121,
                    "numFailsPerLevel": 0.28561,
                    "avgPercentGoodMoves": 0.87128
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.5395918"
                    ],
                    "Nearest Neighbors": [
                        "0.49115646258503404",
                        "0.4906758311693077"
                    ],
                    "Linear SVM": [
                        "0.5061224489795918",
                        "0.465814107944002"
                    ],
                    "RBF SVM": [
                        "0.49795918367346936",
                        "0.49729422771633386"
                    ],
                    "Gaussian Process": [
                        "0.5102040816326531",
                        "0.4952440734090228"
                    ],
                    "Decision Tree": [
                        "0.5142857142857142",
                        "0.5130549362357927"
                    ],
                    "Random Forest": [
                        "0.5115646258503401",
                        "0.507441575075282"
                    ],
                    "Neural Net": [
                        "0.527891156462585",
                        "0.5194514848755171"
                    ],
                    "AdaBoost": [
                        "0.5020408163265306",
                        "0.5014312241990814"
                    ],
                    "Naive Bayes": [
                        "0.5061224489795918",
                        "0.47616651177125324"
                    ],
                    "QDA": [
                        "0.5156462585034014",
                        "0.44435516524890484"
                    ],
                    "LogReg (SKL)": [
                        "0.5265306122448979"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "7": {
                "coefficients": {
                    "(Intercept)": 0.1331,
                    "levelTimes": -0.000009096,
                    "numMovesPerChallenge": 0.002214,
                    "numLevels": 0.1194,
                    "moveTypeChangesPerLevel": -0.01318,
                    "knobStdDevs": 0.03332,
                    "knobAvgs": -0.0113,
                    "percentOFFSET": 0.03653,
                    "percentWAVELENGTH": -0.2638,
                    "percentAMPLITUDE": -0.267,
                    "numFailsPerLevel": -0.004313,
                    "avgPercentGoodMoves": -0.1569
                },
                "stdErrs": {
                    "(Intercept)": 0.9613,
                    "levelTimes": 0.000009235,
                    "numMovesPerChallenge": 0.001635,
                    "numLevels": 0.3315,
                    "moveTypeChangesPerLevel": 0.004747,
                    "knobStdDevs": 0.01547,
                    "knobAvgs": 0.005371,
                    "percentOFFSET": 2.649,
                    "percentWAVELENGTH": 2.615,
                    "percentAMPLITUDE": 2.653,
                    "numFailsPerLevel": 0.003614,
                    "avgPercentGoodMoves": 0.9538
                },
                "pValues": {
                    "(Intercept)": 0.88988,
                    "levelTimes": 0.32461,
                    "numMovesPerChallenge": 0.17574,
                    "numLevels": 0.71877,
                    "moveTypeChangesPerLevel": 0.00548,
                    "knobStdDevs": 0.03131,
                    "knobAvgs": 0.0353,
                    "percentOFFSET": 0.989,
                    "percentWAVELENGTH": 0.91967,
                    "percentAMPLITUDE": 0.91986,
                    "numFailsPerLevel": 0.23264,
                    "avgPercentGoodMoves": 0.8693
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.537415"
                    ],
                    "Nearest Neighbors": [
                        "0.5170068027210885",
                        "0.5165076182334108"
                    ],
                    "Linear SVM": [
                        "0.5061224489795918",
                        "0.465814107944002"
                    ],
                    "RBF SVM": [
                        "0.4993197278911565",
                        "0.49913619406706966"
                    ],
                    "Gaussian Process": [
                        "0.5102040816326531",
                        "0.49475017593244197"
                    ],
                    "Decision Tree": [
                        "0.5047619047619047",
                        "0.5041556437389771"
                    ],
                    "Random Forest": [
                        "0.5482993197278911",
                        "0.5482424321592562"
                    ],
                    "Neural Net": [
                        "0.527891156462585",
                        "0.5217427622211676"
                    ],
                    "AdaBoost": [
                        "0.5306122448979592",
                        "0.5291462834862064"
                    ],
                    "Naive Bayes": [
                        "0.5047619047619047",
                        "0.4713379736343782"
                    ],
                    "QDA": [
                        "0.5156462585034014",
                        "0.4365685252213203"
                    ],
                    "LogReg (SKL)": [
                        "0.5142857142857142"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            },
            "8": {
                "coefficients": {
                    "(Intercept)": 0.2266,
                    "levelTimes": 0.0000018,
                    "numMovesPerChallenge": 0.002106,
                    "numLevels": -0.4578,
                    "moveTypeChangesPerLevel": -0.01209,
                    "knobStdDevs": 0.01169,
                    "knobAvgs": -0.004845,
                    "percentOFFSET": 5.14,
                    "percentWAVELENGTH": 4.458,
                    "percentAMPLITUDE": 4.745,
                    "numFailsPerLevel": -0.003841,
                    "avgPercentGoodMoves": -0.2509
                },
                "stdErrs": {
                    "(Intercept)": 0.9874,
                    "levelTimes": 0.000002687,
                    "numMovesPerChallenge": 0.001598,
                    "numLevels": 0.7692,
                    "moveTypeChangesPerLevel": 0.004589,
                    "knobStdDevs": 0.01241,
                    "knobAvgs": 0.004433,
                    "percentOFFSET": 6.979,
                    "percentWAVELENGTH": 6.983,
                    "percentAMPLITUDE": 7.008,
                    "numFailsPerLevel": 0.003564,
                    "avgPercentGoodMoves": 0.9802
                },
                "pValues": {
                    "(Intercept)": 0.81852,
                    "levelTimes": 0.50294,
                    "numMovesPerChallenge": 0.18761,
                    "numLevels": 0.55173,
                    "moveTypeChangesPerLevel": 0.00843,
                    "knobStdDevs": 0.34622,
                    "knobAvgs": 0.27441,
                    "percentOFFSET": 0.46138,
                    "percentWAVELENGTH": 0.52323,
                    "percentAMPLITUDE": 0.4984,
                    "numFailsPerLevel": 0.28115,
                    "avgPercentGoodMoves": 0.79798
                },
                "numSessions": {
                    "numTrue": 762,
                    "numFalse": 708
                },
                "numSessionsString": "762 / 708<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "percentCorrect": {
                    "Log reg": [
                        "0.524898"
                    ],
                    "Nearest Neighbors": [
                        "0.49387755102040815",
                        "0.49259737837693823"
                    ],
                    "Linear SVM": [
                        "0.5061224489795918",
                        "0.46315642356524145"
                    ],
                    "RBF SVM": [
                        "0.5020408163265306",
                        "0.5020518774978301"
                    ],
                    "Gaussian Process": [
                        "0.5360544217687074",
                        "0.5300123398196488"
                    ],
                    "Decision Tree": [
                        "0.49115646258503404",
                        "0.491184720367333"
                    ],
                    "Random Forest": [
                        "0.5170068027210885",
                        "0.5160383503598701"
                    ],
                    "Neural Net": [
                        "0.5333333333333333",
                        "0.5308809690193338"
                    ],
                    "AdaBoost": [
                        "0.5251700680272109",
                        "0.5251278584990071"
                    ],
                    "Naive Bayes": [
                        "0.4925170068027211",
                        "0.3289420113761309"
                    ],
                    "QDA": [
                        "0.49115646258503404",
                        "0.35255604297793885"
                    ],
                    "LogReg (SKL)": [
                        "0.5333333333333333"
                    ]
                },
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ]
            }
        }'; exit;
    }

    // premade output for multinomialQuestion
    if ($_GET['table'] == 'multinomialQuestion') {
        echo '{
            "1": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.39319727891156464",
                        "0.36834610263320045"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.49523809523809526",
                        "0.34704637928735405"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.47619047619047616",
                        "0.36192280340937766"
                    ],
                    "Random Forest": [
                        "0.5061224489795918",
                        "0.34322719114844485"
                    ],
                    "Neural Net": [
                        "0.507482993197279",
                        "0.343526949241235"
                    ],
                    "AdaBoost": [
                        "0.49387755102040815",
                        "0.36862670696835725"
                    ],
                    "Naive Bayes": [
                        "0.22040816326530613",
                        "0.16959750217676298"
                    ],
                    "QDA": [
                        "0.18503401360544217",
                        "0.14547343964154055"
                    ],
                    "LogReg (SKL)": [
                        "0.5170068027210885"
                    ]
                }
            },
            "2": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.4312925170068027",
                        "0.35180024582005076"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.5006802721088436",
                        "0.34888054610826297"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.48707482993197276",
                        "0.35668076486127653"
                    ],
                    "Random Forest": [
                        "0.5061224489795918",
                        "0.344346484242209"
                    ],
                    "Neural Net": [
                        "0.4965986394557823",
                        "0.34692057549200406"
                    ],
                    "AdaBoost": [
                        "0.49387755102040815",
                        "0.3615897084129878"
                    ],
                    "Naive Bayes": [
                        "0.22448979591836735",
                        "0.1865439576148068"
                    ],
                    "QDA": [
                        "0.20816326530612245",
                        "0.17776560706818217"
                    ],
                    "LogReg (SKL)": [
                        "0.5006802721088436"
                    ]
                }
            },
            "3": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.4666666666666667",
                        "0.394355659707181"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.5061224489795918",
                        "0.3562414157464092"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.5020408163265306",
                        "0.3660818437905644"
                    ],
                    "Random Forest": [
                        "0.507482993197279",
                        "0.3429063043464903"
                    ],
                    "Neural Net": [
                        "0.508843537414966",
                        "0.3485069149782753"
                    ],
                    "AdaBoost": [
                        "0.49115646258503404",
                        "0.36703003686269847"
                    ],
                    "Naive Bayes": [
                        "0.19591836734693877",
                        "0.10990885399248469"
                    ],
                    "QDA": [
                        "0.19183673469387755",
                        "0.12103310132726387"
                    ],
                    "LogReg (SKL)": [
                        "0.47346938775510206"
                    ]
                }
            },
            "4": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.44761904761904764",
                        "0.3663730433116721"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.5047619047619047",
                        "0.3477603841705941"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.4897959183673469",
                        "0.3800007890383949"
                    ],
                    "Random Forest": [
                        "0.5047619047619047",
                        "0.35132949160542326"
                    ],
                    "Neural Net": [
                        "0.5034013605442177",
                        "0.34659573948084244"
                    ],
                    "AdaBoost": [
                        "0.5006802721088436",
                        "0.37302606227568724"
                    ],
                    "Naive Bayes": [
                        "0.19183673469387755",
                        "0.1061101806761333"
                    ],
                    "QDA": [
                        "0.19727891156462585",
                        "0.12577875909573863"
                    ],
                    "LogReg (SKL)": [
                        "0.5129251700680272"
                    ]
                }
            },
            "5": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.47619047619047616",
                        "0.38707893811440514"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.507482993197279",
                        "0.3479170853936747"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.48435374149659866",
                        "0.3631586157762612"
                    ],
                    "Random Forest": [
                        "0.4993197278911565",
                        "0.34657848605781383"
                    ],
                    "Neural Net": [
                        "0.508843537414966",
                        "0.3530433458837607"
                    ],
                    "AdaBoost": [
                        "0.4816326530612245",
                        "0.35974422816833457"
                    ],
                    "Naive Bayes": [
                        "0.43537414965986393",
                        "0.3591684830367712"
                    ],
                    "QDA": [
                        "0.41360544217687073",
                        "0.367921789051435"
                    ],
                    "LogReg (SKL)": [
                        "0.5129251700680272"
                    ]
                }
            },
            "6": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.46258503401360546",
                        "0.3742908288974124"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.4993197278911565",
                        "0.3467892904288152"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.4748299319727891",
                        "0.371293240042357"
                    ],
                    "Random Forest": [
                        "0.507482993197279",
                        "0.3602777777777777"
                    ],
                    "Neural Net": [
                        "0.507482993197279",
                        "0.3521164906243528"
                    ],
                    "AdaBoost": [
                        "0.48707482993197276",
                        "0.3648658212828014"
                    ],
                    "Naive Bayes": [
                        "0.4312925170068027",
                        "0.35421509344478536"
                    ],
                    "QDA": [
                        "0.4122448979591837",
                        "0.3688732163199265"
                    ],
                    "LogReg (SKL)": [
                        "0.5251700680272109"
                    ]
                }
            },
            "7": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.3904761904761905",
                        "0.35283895471084475"
                    ],
                    "Linear SVM": [
                        "0.507482993197279",
                        "0.3429063043464903"
                    ],
                    "RBF SVM": [
                        "0.5047619047619047",
                        "0.35124815438551305"
                    ],
                    "Gaussian Process": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "Decision Tree": [
                        "0.46122448979591835",
                        "0.3812668322440195"
                    ],
                    "Random Forest": [
                        "0.5047619047619047",
                        "0.352369482296633"
                    ],
                    "Neural Net": [
                        "0.5061224489795918",
                        "0.34995606736756335"
                    ],
                    "AdaBoost": [
                        "0.49115646258503404",
                        "0.3718549380921702"
                    ],
                    "Naive Bayes": [
                        "0.4380952380952381",
                        "0.3602390423577071"
                    ],
                    "QDA": [
                        "0.4231292517006803",
                        "0.36648986303212266"
                    ],
                    "LogReg (SKL)": [
                        "0.508843537414966"
                    ]
                }
            },
            "8": {
                "numSessions": {
                    "numA": 762,
                    "numB": 268,
                    "numC": 172,
                    "numD": 268
                },
                "numSessionsString": "762 / 268 / 172 / 268<br>(0.52 expected)",
                "expectedAccuracy": "0.52",
                "algorithmNames": [
                    "Nearest Neighbors",
                    "Linear SVM",
                    "RBF SVM",
                    "Gaussian Process",
                    "Decision Tree",
                    "Random Forest",
                    "Neural Net",
                    "AdaBoost",
                    "Naive Bayes",
                    "QDA",
                    "LogReg (SKL)"
                ],
                "percentCorrect": {
                    "Nearest Neighbors": [
                        "0.45034013605442175",
                        "0.36585653282476915"
                    ],
                    "Linear SVM": [
                        "0.508843537414966",
                        "0.34320555995166324"
                    ],
                    "RBF SVM": [
                        "0.49523809523809526",
                        "0.3431402836490899"
                    ],
                    "Gaussian Process": [
                        "0.507482993197279",
                        "0.34259682212235076"
                    ],
                    "Decision Tree": [
                        "0.49115646258503404",
                        "0.36508981599518747"
                    ],
                    "Random Forest": [
                        "0.507482993197279",
                        "0.35000295770482104"
                    ],
                    "Neural Net": [
                        "0.507482993197279",
                        "0.3573079573079573"
                    ],
                    "AdaBoost": [
                        "0.48707482993197276",
                        "0.35240926527141453"
                    ],
                    "Naive Bayes": [
                        "0.19863945578231293",
                        "0.133033037515416"
                    ],
                    "QDA": [
                        "0.20816326530612245",
                        "0.16589050760076338"
                    ],
                    "LogReg (SKL)": [
                        "0.5006802721088436"
                    ]
                }
            }
        }'; exit;
    }
}
if ($_GET['column'] === 'lvl1') return 'asdf';

// Set ini settings and constants from the config file
$settings = json_decode(file_get_contents("config.json"), true);
ini_set('memory_limit', $settings['memory_limit']);
ini_set('max_execution_time', $settings['max_execution_time']);
ini_set('max_input_vars', $settings['max_input_vars']);
define('DATA_DIR', $settings['DATA_DIR'] . '/' . $_GET['gameID']);
define('PYTHON_DIR', $settings['PYTHON_DIR']);
define('RSCRIPT_DIR', $settings['RSCRIPT_DIR']);

// Get the model file and set some constants
if (isset($_GET['gameID'])) {
    $model = json_decode(file_get_contents("model.json"), true)[$_GET['gameID']];
    define('ALL_LEVELS', $model['levels']);
    define('SQL_QUESTION_CUSTOM', $model['sqlEventCustoms']['question']);
    define('SQL_MOVE_CUSTOM', $model['sqlEventCustoms']['move']);
    define('SQL_OTHER_CUSTOMS', $model['sqlEventCustoms']['other']);
}

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
    $game = $_GET['gameID'];
    if ($game === 'WAVES') {
        $sessionIDs = $sessionsAndTimes['sessions'];
        $moveTypes = ['OFFSET', 'WAVELENGTH', 'AMPLITUDE'];
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
            $infoEventCustoms = array();
            foreach ($sessionAttributes[$sessionID] as $i=>$val) {
                if ($val['level'] > $maxLevel) break;
                $infoTimes[] = $val['time'];
                $infoEventData[] = $val['event_data_complex'];
                $infoLevels[] = $val['level'];
                $infoEvents[] = $val['event'];
                $infoEventCustoms[] = $val['event_custom'];
            }
            $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels, 'event_customs'=>$infoEventCustoms);
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
                $lastType = null;
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
                $numMovesPerChallengePerType = array();
                $numFailsPerLevel = array();
                foreach ($dataObj['levels'] as $i) {
                    $numMovesPerChallenge[$i] = array();
                    $numMovesPerChallengePerType[$i] = array_fill_keys($moveTypes, 0);
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
                        } else if ($dataObj['events'][$i] === 'CUSTOM' && in_array($dataObj['event_customs'][$i], SQL_MOVE_CUSTOM)) {
                            if ($lastType !== $dataJson['slider']) {
                                $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                            }
                            $lastType = $dataJson['slider']; // possible slider values: "AMPLITUDE", "OFFSET", "WAVELENGTH"
                            $numMovesPerChallenge[$dataObj['levels'][$i]][] = $i;
                            if (!isset($numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType])) $numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType] = 0;
                            $numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType]++;
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
            foreach ($moveTypes as $i=>$type) {
                $numMovesPerSliderCols[$type] = array_column($numMovesPerChallengePerType, $type);
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
            foreach ($moveTypes as $i=>$type) {
                $sessionData['features'][$type] = $numMovesPerSliderCols[$type];
                $movesScalar = array_sum($numMoves);
                if ($movesScalar > 0) {
                    $sessionData['features']['percent'.$type] = array_sum($numMovesPerSliderCols[$type]) / $movesScalar;
                } else {
                    $sessionData['features']['percent'.$type] = 0;
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
                if ($jval['event_custom'] === SQL_QUESTION_CUSTOM) {
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
            foreach (ALL_LEVELS as $j=>$lvl) {
                if (!isset($featuresToUse['pgm_'.$lvl]) || !$featuresToUse['pgm_'.$lvl]) continue;
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

            foreach (ALL_LEVELS as $index=>$lvl) {
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

            return array(
                'numLevelsAll'=>$numLevelsAll,
                'numMovesAll'=>$numMovesAll,
                'questionsAll'=>$questionsAll,
                'questionAnswereds'=>$questionAnswereds,
                'basicInfoAll'=>$basicInfoAll,
                'sessionsAndTimes'=>$sessionsAndTimes,
                'levels'=>$levels,
                'numSessions'=>count($sessionsAndTimes['sessions']),
                'questionsTotal'=>$questionsTotal,
                'lvlsPercentComplete'=>$lvlsPercentComplete,
                'numTypeChangesAll'=>$numTypeChangesPerSession,
                'clusters'=>array(
                    'col1'=>$bestColumn1,
                    'col2'=>$bestColumn2,
                    'clusters'=>$clusterPoints,
                    'dunn'=>$bestDunn,
                    'sourceColumns'=>$usedColumns,
                    'eigenvectors'=>$eigenvectors
                ),
                'predictors'=>null,
                'predicted'=>null
            );
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
        if (isset($featureCols['numLevels'])) unset($featureCols['numLevels']);
    } else if ($game === 'CRYSTAL') {
        $sessionIDs = $sessionsAndTimes['sessions'];
        $moveTypes = ['MOLECULE_ROTATE', 'MOLECULE_RELEASE'];
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
            $infoEventCustoms = array();
            foreach ($sessionAttributes[$sessionID] as $i=>$val) {
                if ($val['level'] > $maxLevel) break;
                $infoTimes[] = $val['time'];
                $infoEventData[] = $val['event_data_complex'];
                $infoLevels[] = $val['level'];
                $infoEvents[] = $val['event'];
                $infoEventCustoms[] = $val['event_custom'];
            }
            $dataObj = array('data'=>$infoEventData, 'times'=>$infoTimes, 'events'=>$infoEvents, 'levels'=>$infoLevels, 'event_customs'=>$infoEventCustoms);
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
                $lastType = null;
                $startIndices = array();
                $endIndices = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $knobNumStdDevs = array();
                $knobAmts = array();
                $numMovesPerChallenge = array();
                $moveTypeChangesPerLevel = array();
                $knobStdDevs = array();
                $indicesToSplice = array();
                $levelTimes = array();
                $avgKnobStdDevs = array();
                $knobAvgs = array();
                $numMovesPerChallengePerType = array();
                $numFailsPerLevel = array();
                foreach ($dataObj['levels'] as $i) {
                    $numMovesPerChallenge[$i] = array();
                    $numMovesPerChallengePerType[$i] = array_fill_keys($moveTypes, 0);
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
                        } else if ($dataObj['events'][$i] === 'CUSTOM' && $dataJson['event_custom'] === 'GROW_BTN_PRESS') { // TODO: change later to COMPLETE
                            if (!isset($endIndices[$dataObj['levels'][$i]])) {
                                $endIndices[$dataObj['levels'][$i]] = $i;
                            }
                        } else if ($dataObj['events'][$i] === 'CUSTOM' && in_array($dataObj['event_customs'][$i], SQL_MOVE_CUSTOM)) {
                            if ($lastType !== $dataJson['event_custom']) {
                                $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                            }
                            $lastType = $dataJson['event_custom'];
                            $numMovesPerChallenge[$dataObj['levels'][$i]][] = $i;
                            if (!isset($numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType])) $numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType] = 0;
                            $numMovesPerChallengePerType[$dataObj['levels'][$i]][$lastType]++;
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
            foreach ($moveTypes as $i=>$type) {
                $numMovesPerSliderCols[$type] = array_column($numMovesPerChallengePerType, $type);
            }
            $numFailsPerLevel = array_filter($numFailsPerLevel, function ($index) use ($endIndices) { return in_array($index, array_keys($endIndices)); }, ARRAY_FILTER_USE_KEY);
            $numMoves = array_filter($numMoves, function ($index) use ($endIndices) { return in_array($index, array_keys($endIndices)); }, ARRAY_FILTER_USE_KEY);

            $sessionData = array(
                'avgTime'=>$avgTime,
                'totalTime'=>$totalTime,
                'numMovesPerChallengeArray'=>$numMovesPerChallenge,
                'totalMoves'=>$totalMoves,
                'avgMoves'=>$avgMoves,
                'dataObj'=>$dataObj,
                'features'=>array()
            );

            // add/change features here
            $sessionData['features']['levelTimes'] = $levelTimes;
            $sessionData['features']['numMovesPerChallenge'] = $numMoves;
            $sessionData['features']['numLevels'] = count($levelTimes);
            foreach ($moveTypes as $i=>$type) {
                $sessionData['features'][$type] = $numMovesPerSliderCols[$type];
                $movesScalar = array_sum($numMoves);
                if ($movesScalar > 0) {
                    $sessionData['features']['percent'.$type] = array_sum($numMovesPerSliderCols[$type]) / $movesScalar;
                } else {
                    $sessionData['features']['percent'.$type] = 0;
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
                if ($jval['event_custom'] === SQL_QUESTION_CUSTOM) {
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
        /*
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
            foreach (ALL_LEVELS as $j=>$lvl) {
                if (!isset($featuresToUse['pgm_'.$lvl]) || !$featuresToUse['pgm_'.$lvl]) continue;
                $allData[$sessionID]['features']['pgm_'.$lvl] = $percentGoodMovesAll[$lvl][$i];
            }
            $allData[$sessionID]['features']['percentQuestionsCorrect'] = ($questionsAll['numsQuestions'][$i] === 0) ? 0 : $questionsAll['numsCorrect'][$i] / $questionsAll['numsQuestions'][$i];
        }
        */

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
        }
        $totalTimeAll = array_sum($totalTimesPerLevelAll);
        $totalMovesAll = array_sum($totalMovesPerLevelArray);

        $avgTimeAll = average($totalTimesPerLevelAll);
        $avgMovesAll = average($totalMovesPerLevelArray);

        $basicInfoAll = array(
            'times'=>$totalTimesPerLevelAll,
            'numMoves'=>$totalMovesPerLevelArray,
            'totalTime'=>$totalTimeAll,
            'totalMoves'=>$totalMovesAll,
            'avgTime'=>$avgTimeAll,
            'avgMoves'=>$avgMovesAll,
        );

        if (!isset($column)) {
            $lvlsPercentComplete = array();

            foreach (ALL_LEVELS as $index=>$lvl) {
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
                    [array_column_fixed($featureCols['levelTimes'], $lvl), 'levelTimes', [999999], $lvl],
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

            return array(
                'numLevelsAll'=>$numLevelsAll,
                'numMovesAll'=>$numMovesAll,
                'questionsAll'=>$questionsAll,
                'questionAnswereds'=>$questionAnswereds,
                'basicInfoAll'=>$basicInfoAll,
                'sessionsAndTimes'=>$sessionsAndTimes,
                'levels'=>$levels,
                'numSessions'=>count($sessionsAndTimes['sessions']),
                'questionsTotal'=>$questionsTotal,
                'lvlsPercentComplete'=>$lvlsPercentComplete,
                'clusters'=>array(
                    'col1'=>$bestColumn1,
                    'col2'=>$bestColumn2,
                    'clusters'=>$clusterPoints,
                    'dunn'=>$bestDunn,
                    'sourceColumns'=>$usedColumns,
                    'eigenvectors'=>$eigenvectors
                ),
                'predictors'=>null,
                'predicted'=>null
            );
        }

        // filter features by what should be used for regressions
        foreach ($featureCols as $colName=>$feature) {
            if ((isset($featuresToUse[$colName]) && !$featuresToUse[$colName]) || !isset($featuresToUse[$colName])) {
                unset($featureCols[$colName]);
            }
        }
        // unset features that are calculated but never actually used
        if (isset($featureCols['percentQuestionsCorrect'])) unset($featureCols['percentQuestionsCorrect']);
        if (isset($featureCols['numLevels'])) unset($featureCols['numLevels']);
    } else {
        // do other games here
    }

    // Linear regression stuff
    $regressionVars = array();
    $intercepts = array();
    $coefficients = array();
    $stdErrs = array();
    $significances = array();
    if (!isset($reqSessionID) && $_GET['table'] === 'binomialQuestion') {
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
    } else if ($_GET['table'] === 'levelCompletion') {
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
    } else if ($_GET['table'] === 'numLevels') {
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
    } else if ($_GET['table'] === 'multinomialQuestion') {
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
    $useCache = isset($_GET['useCache']) ? ($_GET['useCache'] === 'true') : true;
    $insertIntoCache = isset($_GET['insertIntoCache']) ? ($_GET['insertIntoCache'] === 'true') : true;
    unset($_GET['useCache']); // unset in $_GET because it's not an actual parameter to the calculations
    unset($_GET['insertIntoCache']);

    // Check if there's a cache entry for this data
    if ($useCache) {
        $cacheQuery = "SELECT output FROM cache WHERE filter=? LIMIT 1";
        $filter = json_encode($_GET);     // these variables are just for clarity
        $cacheQueryParams = array($filter);
        $cacheQueryParamTypes = 's';
        $stmt = queryMultiParam($db, $cacheQuery, $cacheQueryParamTypes, $cacheQueryParams);
    
        if ($stmt === NULL || !$stmt->bind_result($cacheOutput)) {
            http_response_code(500);
            die();
        }
        $stmt->fetch();
        $stmt->close();
    }

    if ($useCache && $cacheOutput) {
        echo $cacheOutput; exit; // exit instead of returning so the string response isn't double json_encoded
    } else {
        if/* binomial qs        */ (!isset($reqSessionID) && ($table === 'basic' || (isset($column) && $table === 'binomialQuestion'))) {
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
                            WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ")
                            GROUP BY session_id
                            HAVING COUNT(*) >= ?
                        ) temp
                    ) AS moves
                ) ";
                array_push($params, ...SQL_MOVE_CUSTOM);
                $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
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
                            WHERE event_custom=?
                            GROUP BY session_id
                            HAVING COUNT(*) >= ?
                        LIMIT ?) temp
                    ) AS questions
                ) ";
                $params[] = SQL_QUESTION_CUSTOM;
                $params[] = $minQuestions;
                $params[] = $maxRows;
                $paramTypes .= 'iii';
            }

            $query .= "ORDER BY a.client_time";

            $stmt = queryMultiParam($db, $query, $paramTypes, $params);
            if($stmt === NULL || !$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
                http_response_code(500);
                die();
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
            $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE' || json_decode($a['event_data_complex'], true)['event_custom'] === 'GROW_BTN_PRESS'; });
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
                $returnArray = $regression;
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
    
                    if (!is_dir(DATA_DIR . '/binomialQuestion')) {
                        mkdir(DATA_DIR . '/binomialQuestion', 0777, true);
                    }
    
                    $numVariables = count(explode(',', $headerString)) + 1;
    
                    $dataFile = DATA_DIR . '/binomialQuestion/binomialQuestionData_'. $questionPredictCol . '_' . $predLevel .'.txt';
                    file_put_contents($dataFile, $predictString);
                    unset($rResults);
                    exec(RSCRIPT_DIR . " scripts/binomialQuestionScript.R " . $column . ' ' . $predLevel . ' ' . $gameID . ' ' . str_replace(',', ' ', $headerString), $rResults);
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
                    $expectedAccuracy = ($numTrue + $numFalse == 0) ? 'NaN' : number_format(max($numTrue, $numFalse) / ($numTrue + $numFalse), 2);
    
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
            }
        } /* single session     */ else if (isset($reqSessionID)) {
            $query =
            "SELECT session_id, level, event, event_custom, event_data_complex, client_time
            FROM log
            WHERE session_id=?
            ORDER BY client_time;";

            $params = array($reqSessionID);
            $stmt = queryMultiParam($db, $query, 's', $params);
            if ($stmt === NULL || !$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time)) {
                http_response_code(500);
                die();
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
            $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE' || json_decode($a['event_data_complex'], true)['event_custom'] === 'GROW_BTN_PRESS'; });
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
                    if ($val['event_custom'] === SQL_QUESTION_CUSTOM) {
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
                    $lastType = null;
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
                                if ($lastType !== $dataJson['slider']) {
                                    if (!isset($moveTypeChangesPerLevel[$dataObj['levels'][$i]])) $moveTypeChangesPerLevel[$dataObj['levels'][$i]] = 0;
                                    $moveTypeChangesPerLevel[$dataObj['levels'][$i]]++;
                                }
                                $lastType = $dataJson['slider'];
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

            $returnArray = array(
                'goalsSingle'=>$goalsSingle,
                'sessionsAndTimes'=>$sessionsAndTimes,
                'basicInfoSingle'=>$basicInfoSingle,
                'graphDataSingle'=>$graphDataSingle,
                'questionsSingle'=>$questionsSingle,
                'levels'=>$levels,
                'numSessions'=>$numSessions
            );
        } /* level completion   */ else if (isset($column) && $table === 'levelCompletion') {
            $predictColumn = $column;
            $startDate = $_GET['startDate'];
            $endDate = $_GET['endDate'];
            $maxRows = $_GET['maxRows'];
            $minMoves = $_GET['minMoves'];
            $colLvl = intval(substr($predictColumn, 3));
            $lvlIndex = array_search($colLvl, ALL_LEVELS);
            $lvlsToUse = array_filter(ALL_LEVELS, function ($a) use($colLvl) { return $a < $colLvl; });
            $isLvl1 = empty($lvlsToUse);

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
                        WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ") AND app_id=? AND session_id IN";
            array_push($params, $startDate, $endDate);
            array_push($params, ...SQL_MOVE_CUSTOM);
            array_push($params, $gameID);
            $paramTypes .= 'ss';
            $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
            $paramTypes .= 's';
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
                        WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ") AND app_id=? AND session_id IN
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
                array_push($params, $minMoves, $maxRows);
                array_push($params, ...SQL_MOVE_CUSTOM);
                array_push($params, $gameID, $gameID, count($lvlsToUse)+1, $minMoves, $maxRows);
                $paramTypes .= 'ii';
                $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
                $paramTypes .= 'ssiii';
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
                        WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ") AND app_id=? AND session_id IN
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
                array_push($params, $minMoves, $maxRows);
                array_push($params, ...SQL_MOVE_CUSTOM);
                array_push($params, $gameID, $gameID, count($lvlsToUse) + 1, $minMoves, $maxRows);
                $paramTypes .= 'ii';
                $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
                $paramTypes .= 'ssiii';
            }

            $stmt = queryMultiParam($db, $query, $paramTypes, $params);
            if($stmt === NULL || !$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
                http_response_code(500);
                die();
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
            $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE' || json_decode($a['event_data_complex'], true)['event_custom'] === 'GROW_BTN_PRESS'; });
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
            $headerString = implode(',', $headerCols);
            $predictString .= $headerString . ",result\n";
            foreach ($predictArray as $i=>$array) {
                $predictString .= $predictColumn . ',' . implode(',', $array) . "\n";
            }

            if (!is_dir(DATA_DIR . '/levelCompletion')) {
                mkdir(DATA_DIR . '/levelCompletion', 0777, true);
            }

            $dataFile = DATA_DIR . '/levelCompletion/levelCompletionData_'. $colLvl .'.txt';
            file_put_contents($dataFile, $predictString);
            unset($rResults);
            $numVariables = count(explode(',', $headerString)) + 1;
            exec(RSCRIPT_DIR . " scripts/levelCompletionScript.R " . $colLvl . ' ' . $gameID . ' ' . str_replace(',', ' ', $headerString), $rResults);
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
            $accuracies[$sklName] = (isset($sklRegOutput[0])) ? array($sklRegOutput[0]) : array(null);

            $trueSessions = array_unique(array_column(array_filter($completeEvents, function ($a) use ($colLvl) { return $a['level'] == $colLvl; }), 'session_id'));
            $numTrue = count($trueSessions); // number of sessions who completed every level including current col
            $numFalse = $numSessions - $numTrue; // number of sessions who completed every level up to but not current col
            $expectedAccuracy = ($numTrue + $numFalse == 0) ? 'NaN' : number_format(max($numTrue, $numFalse) / ($numTrue + $numFalse), 2);

            $returnArray = array(
                'coefficients'=>$coefficients,
                'stdErrs'=>$stdErrs,
                'pValues'=>$pValues,
                'numSessions'=>array('numTrue'=>$numTrue, 'numFalse'=>$numFalse),
                'numSessionsString'=>"$numTrue / $numFalse<br>($expectedAccuracy expected)",
                'expectedAccuracy'=>$expectedAccuracy,
                'percentCorrect'=>array_merge(array('Log reg'=>array($percentCorrectR)), $accuracies),
                'algorithmNames'=>$algorithmNames,
            );
        } /* num levels         */ else if (isset($column) && $table === 'numLevels') {
            $numLevelsColumn = $column;
            $minMoves = $_GET['minMoves'];
            $minQuestions = $_GET['minQuestions'];
            $startDate = $_GET['startDate'];
            $endDate = $_GET['endDate'];

            $colLvl = intval(substr($numLevelsColumn, 3));
            $lvlIndex = array_search($colLvl, ALL_LEVELS);
            $maxRows = $_GET['maxRows'];
            $lvlsToUse = array_filter(ALL_LEVELS, function ($a) use($colLvl) { return $a < $colLvl; });
            $isLvl1 = empty($lvlsToUse);

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
                        WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ") AND app_id=?";
            array_push($params, $startDate, $endDate);
            array_push($params, ...SQL_MOVE_CUSTOM);
            array_push($params, $gameID);
            $paramTypes .= 'ss';
            $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
            $paramTypes .= 's';
            if (!$isLvl1) {
                $query .= "
                        AND session_id IN
                        (
                            SELECT c.session_id FROM log c
                            WHERE c.app_id=? AND (c.event='COMPLETE' OR c.event_custom=3) AND c.level IN (" . implode(",", array_map('intval', $lvlsToUse)) . ")
                            GROUP BY c.session_id
                            HAVING COUNT(DISTINCT c.level) = ?
                        )";

                array_push($params, $gameID, count($lvlsToUse));
                $paramTypes .= 'si';

                $query .= "
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                        LIMIT ?
                    ) a
                )
                ORDER BY a.client_time";
                array_push($params, $minMoves, $maxRows);
                $paramTypes .= 'ii';
            } else {
                $query .= "
                        GROUP BY session_id
                        HAVING COUNT(*) >= ?
                        LIMIT ?
                    ) a
                )
                ORDER BY a.client_time";
                array_push($params, $minMoves, $maxRows);
                $paramTypes .= 'ii';
            }

            $stmt = queryMultiParam($db, $query, $paramTypes, $params);
            if ($stmt === NULL || !$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
                http_response_code(500);
                die();
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
            $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE' || json_decode($a['event_data_complex'], true)['event_custom'] === 'GROW_BTN_PRESS'; });
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
                $headerString = implode(',', $headerCols);

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

                $dataFile = DATA_DIR . '/numLevels/numLevelsData_'. $colLvl .'.txt';
                file_put_contents($dataFile, $predictString);
                unset($rResults);
                exec(RSCRIPT_DIR . " scripts/numLevelsScript.R " . $colLvl . ' ' . $gameID . ' ' . str_replace(',', ' ', $headerString), $rResults);
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

                        $predictionRand = mt_rand(min(...ALL_LEVELS), max(...ALL_LEVELS));
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

            $returnArray = array(
                'coefficients'=>$coefficients,
                'stdErrs'=>$stdErrs, 'pValues'=>$pValues,
                'numSessionsString'=>"$numPredictors",
                'numSessions'=>$numPredictors,
                'percentCorrect'=>array('Log reg'=>array($percentCorrectR), 'Random'=>array($percentCorrectRand))
            );
        } /* multinomial ques   */ else if (isset($column) && $table === 'multinomialQuestion') {
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
                            WHERE event_custom in (" . implode(', ', array_fill(0, count(SQL_MOVE_CUSTOM), '?')) . ")
                            GROUP BY session_id
                            HAVING COUNT(*) >= ?
                        ) temp
                    ) AS moves
                ) ";
                array_push($params, ...SQL_MOVE_CUSTOM);
                $params[] = $minMoves;
                $paramTypes .= str_repeat('i', count(SQL_MOVE_CUSTOM));
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
                            WHERE event_custom=?
                            GROUP BY session_id
                            HAVING COUNT(*) >= ?
                        LIMIT ?) temp
                    ) AS questions
                ) ";
                $params[] = SQL_QUESTION_CUSTOM;
                $params[] = $minQuestions;
                $params[] = $maxRows;
                $paramTypes .= 'iii';
            }

            $query .= "ORDER BY a.client_time";

            $stmt = queryMultiParam($db, $query, $paramTypes, $params);
            if($stmt === NULL || !$stmt->bind_result($session_id, $level, $event, $event_custom, $event_data_complex, $client_time, $app_id)) {
                http_response_code(500);
                die();
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
            $completeEvents = array_filter($allEvents, function($a) { return $a['event'] === 'COMPLETE' || json_decode($a['event_data_complex'], true)['event_custom'] === 'GROW_BTN_PRESS'; });
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
                $headerString = implode(',', $headerCols);
                $predictString .= $headerString . ",result\n";
                foreach ($predictArray as $i=>$array) {
                    $predictString .= $multinomQuestionPredictCol . ',' . implode(',', $array) . "\n";
                }
                if (!is_dir(DATA_DIR . '/multinomialQuestion')) {
                    mkdir(DATA_DIR . '/multinomialQuestion', 0777, true);
                }
                $numVariables = count(explode(',', $headerString)) + 1;
                $dataFile = DATA_DIR . '/multinomialQuestion/multinomialQuestionData_'. $multinomQuestionPredictCol .'_'. $predLevel .'.txt';
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
        } else {
            $returnArray = array('error'=>'Incorrect parameters provided.');
        }

        // Insert this result into the database and then return it
        if ($insertIntoCache) {
            $cacheQuery = "INSERT INTO cache VALUES (?, ?)";
            $cacheQueryParams = array(json_encode($_GET), json_encode($returnArray));
            $cacheQueryParamTypes = 'ss';
            $stmt = queryMultiParam($db, $cacheQuery, $cacheQueryParamTypes, $cacheQueryParams);
            $stmt->close();
        }

        return $returnArray;
    }
}

$db->close();
?>
