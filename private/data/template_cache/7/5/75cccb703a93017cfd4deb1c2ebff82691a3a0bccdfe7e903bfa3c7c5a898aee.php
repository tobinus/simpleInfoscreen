<?php

/* displaySlideShow.twig */
class __TwigTemplate_75cccb703a93017cfd4deb1c2ebff82691a3a0bccdfe7e903bfa3c7c5a898aee extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\"/>
    <!-- testing -->
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">

    <!-- Will update every hour, just in case an error occurs in jquery or errorHandler -->
    <meta http-equiv=\"refresh\" content=\"3600\"/>
    <!-- Jquery: easy access to the DOM -->
    <script src=\"js/jquery-2.1.4.js\"></script>
    <!-- Do not waste any time - register error handler before parsing anything else -->
    <script src=\"js/errorHandler.js\"></script>
    <meta name=\"description\"
          content=\"Infoskjerm som kjører i Fethallen og/eller Fedrelandet\"/>
    <title>";
        // line 16
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "</title>

    <link rel=\"stylesheet\" type=\"text/css\" href=\"infoscreen.css\"/>
</head>

<body id=\"master\">
<div id=\"dummy\">&nbsp;</div>
<!-- TweenLite and its plugins: easy animation -->
<script src=\"js/TweenLite.js\"></script>
<script src=\"js/jquery.gsap.min.js\"></script>
<script src=\"js/CSSPlugin.js\"></script>
<script src=\"js/ScrollToPlugin.js\"></script>
<!-- JavaScript from this application -->
<script src=\"js/slideshow.js\"></script>
<script>
    // Remember the time and date of the last modification
    var TIME_AT_LOAD = ";
        // line 32
        echo twig_escape_filter($this->env, (isset($context["lastModified"]) ? $context["lastModified"] : null), "html", null, true);
        echo ";

    var slideShow = ";
        // line 34
        echo (isset($context["slideShowJs"]) ? $context["slideShowJs"] : null);
        echo ";

    var ENABLE_SLIDE_PROGRESS_BAR = ";
        // line 36
        echo twig_escape_filter($this->env, (isset($context["useProgressBarJs"]) ? $context["useProgressBarJs"] : null), "html", null, true);
        echo ";

    var ENABLE_TOTAL_PROGRESS_TEXT = ";
        // line 38
        echo twig_escape_filter($this->env, (isset($context["showSlideNumberJs"]) ? $context["showSlideNumberJs"] : null), "html", null, true);
        echo ";

    // Number of seconds between each time the server is asked whether the slideshow is updated
    var UPDATE_CHECK_INTERVAL = ";
        // line 41
        echo twig_escape_filter($this->env, (isset($context["secondsBetweenUpdateChecks"]) ? $context["secondsBetweenUpdateChecks"] : null), "html", null, true);
        echo ";

    // Number of seconds to wait for a slide to (unsuccessfully) load, before skipping it
    // NOTE: In Chrome, a \"page not found\" counts as successful page load
    var SLIDE_LOAD_TIMEOUT = ";
        // line 45
        echo twig_escape_filter($this->env, (isset($context["slideLoadTimeout"]) ? $context["slideLoadTimeout"] : null), "html", null, true);
        echo ";
</script>
<script
        src=\"js/";
        // line 48
        echo twig_escape_filter($this->env, (isset($context["transition"]) ? $context["transition"] : null), "html", null, true);
        echo ".transitions.SlideShow.js\"></script>
";
        // line 49
        if ((((isset($context["transition"]) ? $context["transition"] : null) != "cut") && ((isset($context["slideShowSize"]) ? $context["slideShowSize"] : null) == 1))) {
            echo "<script src=\"js/cut.transitions.SlideShow.js\"></script>";
        }
        // line 50
        echo "<script>
    var transition = ";
        // line 51
        echo twig_escape_filter($this->env, (isset($context["transition"]) ? $context["transition"] : null), "html", null, true);
        echo "Transition;
    ";
        // line 52
        if (((isset($context["slideShowSize"]) ? $context["slideShowSize"] : null) == 1)) {
            // line 53
            echo "            transition.startTransitionS2S = cutTransition.startTransitionS2S;
    ";
        }
        // line 55
        echo "</script>


<div id=\"slideProgress\" class=\"ui\"></div>
<div id=\"slideShowProgress\" class=\"ui\" style=\"opacity:0\">
    ";
        // line 60
        if ((isset($context["showSlideNumber"]) ? $context["showSlideNumber"] : null)) {
            // line 61
            echo "1/";
            echo twig_escape_filter($this->env, (isset($context["slideShowSize"]) ? $context["slideShowSize"] : null), "html", null, true);
        }
        // line 63
        echo "</div>
<div id=\"iframe-container\">
    <iframe id=\"current\" scrolling=\"no\"></iframe>

    <iframe id=\"next\" scrolling=\"no\"></iframe>
</div>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "displaySlideShow.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  123 => 63,  119 => 61,  117 => 60,  110 => 55,  106 => 53,  104 => 52,  100 => 51,  97 => 50,  93 => 49,  89 => 48,  83 => 45,  76 => 41,  70 => 38,  65 => 36,  60 => 34,  55 => 32,  36 => 16,  19 => 1,);
    }
}
