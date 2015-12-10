<?php

/* fotballkamper.twig */
class __TwigTemplate_b572b938b491387c20cdd7dbed9ba96d951ffce6b7ede1530659fd178b07084d extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'header' => array($this, 'block_header'),
            'list' => array($this, 'block_list'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "﻿<!DOCTYPE html>
<html>
    <head>
        ";
        // line 4
        $this->displayBlock('head', $context, $blocks);
        // line 131
        echo "    </head>
    
    <body>
    <div class=\"container-fluid\">
        ";
        // line 135
        $this->displayBlock('header', $context, $blocks);
        // line 149
        echo "        <div class=\"row\">
                ";
        // line 150
        $this->displayBlock('list', $context, $blocks);
        // line 193
        echo "        </div>
        ";
        // line 194
        $this->displayBlock('footer', $context, $blocks);
        // line 203
        echo "    </div>
    </body>
</html>";
    }

    // line 4
    public function block_head($context, array $blocks = array())
    {
        // line 5
        echo "        <title>Fotballkamper</title>
        <meta charset=\"UTF-8\">
        <script src=\"js/jquery-2.1.4.js\"></script>

        <script>
            function getDate() {
                return new Date();
            }
            function updateMinute() {
                var date = getDate();
                var minutes = date.getMinutes();
                if (minutes < 10) {
                    minutes = \"0\" + minutes;
                }
                document.getElementById('minutt' ).innerHTML = \"\" + minutes;
            }
            function updateHour() {
                var date = getDate();
                var hours = date.getHours();
                if (hours < 10) {
                    hours = \"0\" + hours;
                }
                document.getElementById('time' ).innerHTML = \"\" + hours;
            }
            function startClock() {
                var date = getDate();
                // set current value
                updateHour();
                updateMinute();
                var untilNextMinute = 60 - date.getSeconds();
                var untilNextHour = 60 - date.getMinutes();
                setTimeout(function () {
                    setInterval(updateMinute, 60000);
                    updateMinute();
                }, untilNextMinute * 1000);
                setTimeout(function () {
                    setInterval(updateHour, 3600000);
                    updateHour();
                }, (untilNextHour * 60000) - (date.getSeconds() * 1000));
            }

            \$( document ).ready(function () {
                startClock();
            });

        </script>

        <!-- Latest compiled CSS -->
        <link rel=\"stylesheet\" href=\"bootstrap/css/bootstrap.css\">

        <!-- Optional theme -->
        <link rel=\"stylesheet\" href=\"bootstrap/css/bootstrap-theme.css\">

        <style>
           /* col {
                width: fit-content;
                width: -moz-fit-content;
                width: -webkit-fit-content;
            }
            col#strek {
                width: 1em;
            }
            col#bortelag {
                width: available;
                width: fill-available;
                width: -moz-available;
            } */

            tr.active {
                font-weight: bold;
            }

            @keyframes blink {
                0% { opacity: 1.0; }
                50% { opacity: 0.0; }
                100% { opacity: 1.0; }
            }

            @-webkit-keyframes blink {
                0% { opacity: 1.0; }
                50% { opacity: 0.0; }
                100% { opacity: 1.0; }
            }

            #kolon {
                animation: blink 4s step-start 0s infinite;
                -webkit-animation: blink 4s step-start 0s infinite;
            }

            body {
                min-width: 1872px;
                min-height: 1048px;
            }
            /*
            body {
                font-size: 2em;
            }
            h1 {
                font-size: 3em;
            }

            h1 small {
                font-size: 1.6em;
            }

            table {
                font-size: 2.3em;
            } */
            h2 {
                font-weight: bold;
            }
            body {
                font-family: \"Liberation Sans\", sans-serif;
            }
            table {
                font-weight: 700;
            }
            tbody, tfoot {
                font-size: 42px;
            }

        </style>

        <!-- Latest compiled and minified JavaScript -->
        <!-- <script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js\"></script> -->
        ";
    }

    // line 135
    public function block_header($context, array $blocks = array())
    {
        // line 136
        echo "        <div class=\"row first\">
            <div class=\"col-lg-12 clearfix\" style=\"margin-top:10px\">
                <span class=\"glyphicon glyphicon-info-sign pull-left text-info\" aria-hidden=\"true\" style=\"font-size: 115px\"></span>
                <h2 class=\"pull-left text-info\" style=\"vertical-align: middle\">&nbsp;&nbsp;Fotballkamper på Fedrelandet</h2>
                <h2 class=\"text-muted pull-right\" style=\"vertical-align: middle\">
                    <span id=\"time\"></span
                            ><span id=\"kolon\">:</span
                        ><span id=\"minutt\"></span
                    >
                </h2>
            </div>
        </div>
        ";
    }

    // line 150
    public function block_list($context, array $blocks = array())
    {
        // line 151
        echo "                    ";
        if ((isset($context["kamper"]) ? $context["kamper"] : null)) {
            // line 152
            echo "            <div class=\"col-lg-12\">
                <table style=\"width: 100%;\" class=\"table table-striped\" id=\"kamper\">
                    <colgroup>
                        <col id=\"kampstart\"/>
                        <col id=\"hjemmelag\"/>
                        <col id=\"strek\"/>
                        <col id=\"bortelag\"/>
                        <col id=\"avdeling\"/>
                        <col id=\"bane\"/>
                        ";
            // line 163
            echo "                    </colgroup>
                    <thead>
                    <tr>
                        <th>Kampstart</th>
                        <th>Hjemmelag</th>
                        <th>–</th>
                        <th>bortelag</th>
                        <th>Avdeling</th>
                        <th>Bane</th>
                        ";
            // line 173
            echo "                    </tr>
                    </thead>
                    <tbody>
                    ";
            // line 176
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["kamper"]) ? $context["kamper"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["kamp"]) {
                // line 177
                echo "                    <tr>
                        <td>";
                // line 178
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["kamp"], "starttid", array()), "H:i"), "html", null, true);
                echo "</td>
                        <td>";
                // line 179
                echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "hjemmelag", array()), "html", null, true);
                echo "</td><td> – </td><td>";
                echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "bortelag", array()), "html", null, true);
                echo "</td>
                        <td>";
                // line 180
                echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "avdeling", array()), "html", null, true);
                echo "</td>
                        <td>";
                // line 181
                echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "bane", array()), "html", null, true);
                echo "</td>
                    </tr>
                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['kamp'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 184
            echo "                    </tbody>
                </table>
            </div>
                        ";
        } else {
            // line 188
            echo "                        <div class=\"col-md-6 col-md-offset-3\">
                            <h3>Ingen kamper <small>i dag</small></h3>
                        </div>
                ";
        }
        // line 192
        echo "                ";
    }

    // line 194
    public function block_footer($context, array $blocks = array())
    {
        // line 195
        echo "            <div class=\"row\" style=\"position: absolute; bottom: 0; width: 100%\">
                <div class=\"col-md-6\">
                    <p class=\"text-muted\"><small>Hentet fra fotball.no</small></p>
                </div>
                <div class=\"col-md-6\"></div>
            </div>

        ";
    }

    public function getTemplateName()
    {
        return "fotballkamper.twig";
    }

    public function getDebugInfo()
    {
        return array (  287 => 195,  284 => 194,  280 => 192,  274 => 188,  268 => 184,  259 => 181,  255 => 180,  249 => 179,  245 => 178,  242 => 177,  238 => 176,  233 => 173,  222 => 163,  211 => 152,  208 => 151,  205 => 150,  189 => 136,  186 => 135,  57 => 5,  54 => 4,  48 => 203,  46 => 194,  43 => 193,  41 => 150,  38 => 149,  36 => 135,  30 => 131,  28 => 4,  23 => 1,);
    }
}
