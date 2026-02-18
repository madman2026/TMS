<?php

namespace Modules\Core\Traits;

trait RuntimeEnv
{
    protected function setup()
    {
        $runtimeBase = base_path('webdriver');
        $browsersDir = 'C:\Users\Neo\AppData\Local\ms-playwright';

        $this->env('SystemRoot', 'C:\\Windows');
        $this->env('WINDIR', 'C:\\Windows');
        $this->env('TEMP', $runtimeBase.'\temp');
        $this->env('TMP', $runtimeBase.'\temp');
        $this->env('TMPDIR', $runtimeBase.'\temp');
        $this->env('USERPROFILE', $runtimeBase.'\home');
        $this->env('HOME', $runtimeBase.'\home');
        $this->env('LOCALAPPDATA', $runtimeBase.'\local');
        $this->env('APPDATA', $runtimeBase.'\local');
        $this->env('PLAYWRIGHT_BROWSERS_PATH', $browsersDir);
        $this->env('PLAYWRIGHT_NODE_PATH' , 'C:\Program Files\nodejs\node.exe');

    }

    private function env(string $key, string $value): void
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
