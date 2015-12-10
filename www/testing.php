<?php
/*
$eksempel = mb_eregi_replace('<script>[\d\D\n]+</script>|\{\{[\d\D\n]+\}\}|\{\%[\d\D\n]+\%\}', '', file_get_contents('local/trening.html'));

$xml = new DOMDocument();
$xml->loadHTML($eksempel);
echo $xml->textContent;
*/
?>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--[if IE]>
    <style>
        #frame {
            zoom: 0.1;
        }
    </style>
    <![endif]-->
    <style>
        @media only all {
            .frameContainer, .frame {
                display: none;
            }
        }
        @media only screen {
            .frameContainer, .frame {
                overflow: hidden;
                display: block
            }
            .frameContainer {
                border: solid 1px darkgrey;
                padding: 0;
            }
            .frame {
                padding: 0;
                margin: 0;
                width: 1920px;
                height: 1080px;
                border: none;
                transform-origin: 0 0;
            }
        }

        @media only screen {
            .frameContainer {
                width: 96px;
                height: 54px;
            }

            .frame {
                -ms-transform: scale(0.05, 0.05); /* IE 9 */
                -webkit-transform: scale(0.05, 0.05); /* Safari */
                transform: scale(0.05, 0.05);
            }
        }

        @media only screen and (min-device-width: 450px) {
            .frameContainer {
                width: 192px;
                height: 108px;
            }

            .frame {
                -ms-transform: scale(0.1, 0.1); /* IE 9 */
                -webkit-transform: scale(0.1, 0.1); /* Safari */
                transform: scale(0.1, 0.1);
            }
        }

        @media only screen and (min-device-width: 900px) and (min-device-height: 700px) {
            .frameContainer {
                width: 288px;
                height: 162px;
            }

            .frame {
                -ms-transform: scale(0.15, 0.15); /* IE 9 */
                -webkit-transform: scale(0.15, 0.15); /* Safari */
                transform: scale(0.15, 0.15);
            }
        }
    </style>
</head>

<body>
<div class="frameContainer"><iframe class="frame" src="local/2.html" scrolling="no"></iframe></div>
<div class="frameContainer"><iframe class="frame" src="local/trening.html" scrolling="no"></iframe></div>
<div class="frameContainer"><iframe class="frame" src="local/banedagbok.html" scrolling="no"></iframe></div>
<p style="width: 50px">So, I wanted to test scrolling, so I am making sure that this makes the
browser activate its zoom capability. I'm pretty sure this is long enough.</p>
</body>

</html>
