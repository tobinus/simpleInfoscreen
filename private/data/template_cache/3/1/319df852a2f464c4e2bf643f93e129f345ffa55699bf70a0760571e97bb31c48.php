<?php

/* banedagbok.twig */
class __TwigTemplate_319df852a2f464c4e2bf643f93e129f345ffa55699bf70a0760571e97bb31c48 extends Twig_Template
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
        echo "﻿";
        $context["sammeDag"] = (twig_date_modify_filter($this->env, (isset($context["dato"]) ? $context["dato"] : null), "midnight") == twig_date_converter($this->env, "today"));
        // line 2
        $context["dagSlutt"] = (((isset($context["kamper"]) ? $context["kamper"] : null)) ? (twig_date_format_filter($this->env, twig_date_modify_filter($this->env, $this->getAttribute(twig_last($this->env, (isset($context["kamper"]) ? $context["kamper"] : null)), "starttid", array()), "+1 hour"), "H:i")) : ("00:00"));
        // line 3
        echo "<!DOCTYPE html>
<html>
    <head>
        ";
        // line 6
        $this->displayBlock('head', $context, $blocks);
        // line 157
        echo "    </head>
    
    <body>
    <div class=\"container-fluid\">
        ";
        // line 161
        $this->displayBlock('header', $context, $blocks);
        // line 175
        echo "        <div class=\"row\">
            <div class=\"col-lg-10 col-lg-offset-1\">
                ";
        // line 177
        $this->displayBlock('list', $context, $blocks);
        // line 224
        echo "                ";
        if ( !(isset($context["kamper"]) ? $context["kamper"] : null)) {
            // line 225
            echo "                <h3 class=\"text-center\">Det er ingen håndballkamper
                    ";
            // line 226
            if ((isset($context["sammeDag"]) ? $context["sammeDag"] : null)) {
                // line 227
                echo "                        i dag.
                    ";
            } else {
                // line 229
                echo "                        ";
                echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('day')->getCallable(), array((isset($context["dato"]) ? $context["dato"] : null), "nob", "f")), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, (isset($context["dato"]) ? $context["dato"] : null), "d.m.Y"), "html", null, true);
                echo ".
                    ";
            }
            // line 230
            echo "</h3>
                ";
        }
        // line 232
        echo "            </div>
        </div>
        ";
        // line 234
        $this->displayBlock('footer', $context, $blocks);
        // line 242
        echo "    </div>
    </body>
</html>";
    }

    // line 6
    public function block_head($context, array $blocks = array())
    {
        // line 7
        echo "        <title>Banedagbok</title>
        <meta charset=\"UTF-8\">
        <script src=\"js/jquery-2.1.4.js\"></script>

        <script>
            function getDate() {
                return new Date();
            }
            function updateMinute() {
                var date = getDate();
                var minutes = date.getMinutes();
                if (minutes % 5 == 0) {
                    updateRowAppearance(date);
                }
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
                updateRowAppearance(date);
            }

            function updateRowAppearance(date)
            {
                var getTime = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2);
                var markedPrimary = false;
                var previous;
                \$( '#kamper' ).find( 'tbody' ).children().each( function ()
                {
                    var \$this = \$( this );
                    if (\$this.children().html() <= getTime)
                    {
                        \$this.removeClass( 'bg-primary' ).addClass( 'text-muted' );
                    } else if (!markedPrimary)
                    {
                        if (previous != null)
                        {
                            previous.removeClass( 'text-muted' ).addClass( 'bg-primary' );
                        }
                        markedPrimary = true;
                    }
                    previous = \$this;
                } );
                if (\"";
        // line 73
        echo twig_escape_filter($this->env, (isset($context["dagSlutt"]) ? $context["dagSlutt"] : null), "html", null, true);
        echo "\" > getTime && !markedPrimary) {
                    // The last match is still active, but it is marked as done
                    previous.removeClass( 'text-muted' ).addClass( 'bg_primary' );
                }
            }

            ";
        // line 79
        if ((isset($context["sammeDag"]) ? $context["sammeDag"] : null)) {
            // line 80
            echo "            \$( document ).ready(function () {
                startClock();
            });
            ";
        }
        // line 84
        echo "
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
            body {
                font-family: \"Liberation Sans\", sans-serif;
            }

        </style>

        <!-- Latest compiled and minified JavaScript -->
        <!-- <script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js\"></script> -->
        ";
    }

    // line 161
    public function block_header($context, array $blocks = array())
    {
        // line 162
        echo "        <div class=\"row first\">
            <div class=\"col-lg-12 clearfix\" style=\"margin-top:10px\">
                <span class=\"glyphicon glyphicon-info-sign pull-left text-info\" aria-hidden=\"true\" style=\"font-size: 115px\"></span>
                <h2 class=\"pull-left text-info\" style=\"vertical-align: middle\">&nbsp;&nbsp;Håndballkamper i Fethallen</h2>
                <h2 class=\"text-muted pull-right\" style=\"vertical-align: middle\">
        ";
        // line 167
        if ( !(isset($context["sammeDag"]) ? $context["sammeDag"] : null)) {
            echo twig_escape_filter($this->env, call_user_func_array($this->env->getFilter('day')->getCallable(), array((isset($context["dato"]) ? $context["dato"] : null), "nob", "f")), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, (isset($context["dato"]) ? $context["dato"] : null), "d.m.y"), "html", null, true);
        } else {
            echo "<span id=\"time\"></span
                            ><span id=\"kolon\">:</span
                        ><span id=\"minutt\"></span
                    >";
        }
        // line 171
        echo "                </h2>
            </div>
        </div>
        ";
    }

    // line 177
    public function block_list($context, array $blocks = array())
    {
        // line 178
        echo "                <table style=\"width: 100%; ";
        if ( !(isset($context["kamper"]) ? $context["kamper"] : null)) {
            echo "opacity: 0; ";
        }
        echo "\" class=\"table table-striped\" id=\"kamper\">
                    <colgroup>
                        <col id=\"kampstart\"/>
                        <col id=\"hjemmelag\"/>
                        <col id=\"strek\"/>
                        <col id=\"bortelag\"/>
                        <col id=\"avdeling\"/>
                        <col id=\"hjemmegarderobe\"/>
                        <col id=\"bortegarderobe\"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th rowspan=\"2\">Kampstart</th>
                        <th rowspan=\"2\">Hjemmelag</th>
                        <th rowspan=\"2\">–</th>
                        <th rowspan=\"2\">bortelag</th>
                        <th rowspan=\"2\">Turnering</th>
                        <th colspan=\"2\">Garderober</th>
                    </tr>
                    <tr>

                        <th>hjemme</th>
                        <th>borte</th>
                    </tr>
                    </thead>
                    ";
        // line 211
        echo "                    <tbody>
                    ";
        // line 212
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["kamper"]) ? $context["kamper"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["kamp"]) {
            // line 213
            echo "                    <tr>
                        <td>";
            // line 214
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($context["kamp"], "starttid", array()), "H:i"), "html", null, true);
            echo "</td>
                        <td>";
            // line 215
            echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "hjemmelag", array()), "html", null, true);
            echo "</td><td> – </td><td>";
            echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "bortelag", array()), "html", null, true);
            echo "</td>
                        <td>";
            // line 216
            echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "avdeling", array()), "html", null, true);
            echo "</td>
                        <td>";
            // line 217
            echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "hjemmegarderobe", array()), "html", null, true);
            echo "</td>
                        <td>";
            // line 218
            echo twig_escape_filter($this->env, $this->getAttribute($context["kamp"], "bortegarderobe", array()), "html", null, true);
            echo "</td>
                    </tr>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['kamp'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 221
        echo "                    </tbody>
                </table>
                ";
    }

    // line 234
    public function block_footer($context, array $blocks = array())
    {
        // line 235
        echo "            <div class=\"row\" style=\"position: fixed; bottom: 0; width: 100%; z-index: -1\">
                <div class=\"col-md-6\">
                    <p class=\"text-muted\"><small>Hentet fra handball.no</small></p>
                </div>
                <div class=\"col-md-6\"></div>
            </div>
        ";
    }

    public function getTemplateName()
    {
        return "banedagbok.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  357 => 235,  354 => 234,  348 => 221,  339 => 218,  335 => 217,  331 => 216,  325 => 215,  321 => 214,  318 => 213,  314 => 212,  311 => 211,  280 => 178,  277 => 177,  270 => 171,  259 => 167,  252 => 162,  249 => 161,  173 => 84,  167 => 80,  165 => 79,  156 => 73,  88 => 7,  85 => 6,  79 => 242,  77 => 234,  73 => 232,  69 => 230,  61 => 229,  57 => 227,  55 => 226,  52 => 225,  49 => 224,  47 => 177,  43 => 175,  41 => 161,  35 => 157,  33 => 6,  28 => 3,  26 => 2,  23 => 1,);
    }
}
