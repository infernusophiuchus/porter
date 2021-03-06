<?php
/**
 *    Porter 0.2.2
 *    Copyright (C) 2021  Dmitry Shumilin
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace Infernusophiuchus\Porter;

use Infernusophiuchus\Porter\Handlers\DirectoryHandler;
use Infernusophiuchus\Porter\Handlers\Cleaner;
use Infernusophiuchus\Porter\Exceptions\MainException;
use Infernusophiuchus\Porter\Exceptions\DirectoryHandlerException;
use Infernusophiuchus\Porter\Exceptions\CleanerException;

class Main
{

    protected $dist;
    protected $deploy;

    public function __construct(string $basedir, string $dist, string $deploy)
    {

        if (substr($basedir, -1) === '/' ||
            substr($basedir, -1) === '\\') $basedir = substr($basedir, 0, -1);
        
        if (!empty($basedir) && !is_dir($basedir)) throw new MainException(
            MainException::INVALID_BASEDIR_MESSAGE,
            MainException::INVALID_BASEDIR_CODE
        );

        foreach (['dist', 'deploy'] as $dir) {

            $this->$dir = $basedir;

            if ($$dir !== '/' ||
                $$dir !== '\\') {

                $this->$dir .= $$dir;

                if (substr($this->$dir, -1) === '/' ||
                    substr($this->$dir, -1) === '\\') $this->$dir = substr($this->$dir, 0, -1);

                if (!is_dir($this->$dir)) {

                    switch ($dir) {

                        case 'dist':
                            $message = '"'.$this->$dir.'" — '.MainException::INVALID_DIST_MESSAGE;
                            $code = MainException::INVALID_DIST_CODE;
                            break;

                        case 'deploy':
                            $message = '"'.$this->$dir.'" — '.MainException::INVALID_DEPLOY_MESSAGE;
                            $code = MainException::INVALID_DEPLOY_CODE;
                            break;

                    }

                    throw new MainException($message, $code);

                }

            }

        }

        if ($this->dist === $this->deploy) throw new MainException(
            MainException::DIST_DEPLOY_EQUAL_MESSAGE,
            MainException::DIST_DEPLOY_EQUAL_CODE
        );

    }

    /**
     * Run the command.
     * 
     * @param string $command
     * 
     * @return void
     */
    public function command(string $command) : void
    {

        switch ($command) {

            case 'deploy':
                $this->deploy();
                break;

            case 'depclean':
                $this->deployClean();
                break;

            case 'help':
                echo "\n";
                echo "deploy — Deploy the application.\n";
                echo "depclean — Delete previous deploy.\n";
                break;

            default:
                echo "\nInvalid command.\n";
                break;

        }

    }

    /**
     * Deploy the app.
     * 
     * @param bool $tfi
     * If true, the method will transform index.html to index.php.
     * 
     * @return void
     * 
     * @throws MainException
     */
    public function deploy(bool $tfi = true) : void
    {

        try {

            $dh = new DirectoryHandler($this->dist, $this->deploy);

            $deployed = $dh->copyAll();

            if ($tfi) {

                if (file_exists($this->deploy.'/index.html')) {
                    
                    if (rename(
                        $this->deploy.'/index.html',
                        $this->deploy.'/index.php'
                    )) {

                        $i = array_search($this->deploy.'/index.html', $deployed['files']);

                        if (is_int($i)) $deployed['files'][$i] = $this->deploy.'/index.php';

                    }
            
                }

            }

            file_put_contents(
                $this->deploy.'/deployed.json',
                json_encode($deployed)
            );

            echo "\nThe application deploying completed.\n";

        } catch (DirectoryHandlerException $e) {}

        if (isset($e)) throw new MainException($e->getMessage(), $e->getCode());

    }

    /**
     * Removes app previous deployment.
     * 
     * @return void
     * 
     * @throws MainException
     */
    public function deployClean() : void
    {

        try {

            $cleaner = new Cleaner($this->deploy);
            $cleaner->removeDeploy();

        } catch (CleanerException $e) {}

        if (isset($e)) throw new MainException($e->getMessage(), $e->getCode());

    }

}
