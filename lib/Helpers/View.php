<?php

namespace Mikrofraim\Helpers;

class View
{
    /**
     * Twig_Environment
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(
        string $templatesDirectory,
        bool $debug = false,
        bool $cache = false,
        string $cachePath = '../storage/cache/twig'
    ) {
        $twig = null;

        $twigLoader = new \Twig_Loader_Filesystem($templatesDirectory);

        $twig = new \Twig_Environment($twigLoader, [
            'cache' => ($cache === true) ? $cachePath : false,
            'debug' => $debug
        ]);

        $twig->addGlobal('session', isset($_SESSION) ? $_SESSION : null);
        $twig->addGlobal('server', $_SERVER);

        $twig->addExtension(new \Twig_Extension_Debug());

        $this->twig = $twig;
    }


    /**
     * Render a twig template
     * @param  string $template
     * @param  array|null $data
     * @return string
     */
    public function render(string $template, $data = null): string
    {
        if ($data === null) {
            $data = [];
        }

        if (\Session::get('_redirectData') !== null) {
            $redirectData = is_array(\Session::get('_redirectData'))
                            ? \Session::get('_redirectData') : [];
            $data = array_merge($data, $redirectData);
        }

        if ($this->twig === null) {
            throw new \Exception('Twig not loaded');
        }

        return $this->twig->render($template, $data);
    }

    /**
     * Preform a redirect
     * @param  string $path
     * @param  mixed $data
     * @param  mixed $code
     */
    public function redirect(string $path, $data = null, $code = null): void
    {
        if ($data !== null) {
            /* backwards compatiblity with previous versions where
             * $code was 2nd argument */
            if (is_integer($data)) {
                if ($data >= 0 && $data <= 999) {
                    $code = $data;
                    $data = [];
                }
            }

            if (!is_array($data)) {
                throw new \Exception('Redirect data must be of type array');
            }

            \Session::set('_redirectData', $data);
        }

        if ($code !== null) {
            header('HTTP/1.1 ' . $code);
        }

        \Session::set('_isRedirect', true);

        header('Location: ' . $path);
    }
}
