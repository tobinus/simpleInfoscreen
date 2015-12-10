<?php

/* index.twig */
class __TwigTemplate_20f238722f24f079d03456dd591f3ba8f77aa6beefcacd99808bd7254728b317 extends Twig_Template
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
    <title>";
        // line 5
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
        echo "</title>
    <script src=\"js/jquery-2.1.4.js\"></script>
    <script>
        var timeout = 60 * 1000; // one minute
        var reloadTimer;

        function startReloadTimer()
        {
            reloadTimer = setTimeout(reloadPage, timeout);
        }

        function resetReloadTimer()
        {
            clearTimeout(reloadTimer);
            startReloadTimer();
        }

        function reloadPage()
        {
            var iframe = document.getElementById('i');
            iframe.setAttribute('src', 'about:blank');
            iframe.setAttribute('src', 'displaySlideShow.php?";
        // line 26
        echo twig_escape_filter($this->env, (isset($context["query"]) ? $context["query"] : null), "html", null, true);
        echo "');
            startReloadTimer();
        }

        startReloadTimer();
    </script>
    <script>
        function checkForUpdate()
        {
            \$.ajax({
                url: '/',
                type: 'GET',
                data: {
                    checkUpdate: ";
        // line 39
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, (isset($context["lastUpdate"]) ? $context["lastUpdate"] : null), "js"), "html", null, true);
        echo ",
                },
                dataType: 'text',
                mimeType: 'text/plain',
                success: function ( text )
                {
                    if (text == 'true') {
                        location.reload(true);
                    }
                }
            });
        }

        setInterval(checkForUpdate, (";
        // line 52
        echo twig_escape_filter($this->env, twig_escape_filter($this->env, (isset($context["updateCheckInterval"]) ? $context["updateCheckInterval"] : null), "js"), "html", null, true);
        echo " * 1000));
    </script>
    <style>
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            overflow: hidden;
            margin: 0;
        }
        body {
            width: 100%;
            height: 100%;
            width: 100vw;
            height: 100vh;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
    </style>
</head>
<body>
<iframe src=\"displaySlideShow.php?";
        // line 74
        echo twig_escape_filter($this->env, (isset($context["query"]) ? $context["query"] : null), "html", null, true);
        echo "\" scrolling=\"no\" id=\"i\"></iframe>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  106 => 74,  81 => 52,  65 => 39,  49 => 26,  25 => 5,  19 => 1,);
    }
}
