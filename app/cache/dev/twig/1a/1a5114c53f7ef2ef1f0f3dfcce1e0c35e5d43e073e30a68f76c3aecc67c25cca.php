<?php

/* FreeadsFreeadsBundle:Default:index.html.twig */
class __TwigTemplate_f8c3ab89226ca8032e064b2cbdf29a0ac6c66932477d3c5b5ba952434a5adf1d extends Twig_Template
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
        echo "<html>
\t<body>
\t\tHello ";
        // line 3
        echo twig_escape_filter($this->env, (isset($context["name"]) ? $context["name"] : $this->getContext($context, "name")), "html", null, true);
        echo "!
\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "FreeadsFreeadsBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  23 => 3,  19 => 1,);
    }
}
/* <html>*/
/* 	<body>*/
/* 		Hello {{ name }}!*/
/* 	</body>*/
/* </html>*/
