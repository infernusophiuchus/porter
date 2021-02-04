<?php
/**
 *    Porter 0.0.3
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

use Infernusophiuchus\Porter\Exceptions\MainException;

class Main
{

    protected $dist;
    protected $deploy;

    public function __construct(string $basedir, string $dist, string $deploy, string $command)
    {

        if (substr($basedir, -1) === '/' ||
            substr($basedir, -1) === '\\') $basedir = substr($basedir, 0, -1);
        
        if (!is_dir($basedir)) throw new MainException(
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
                            $message = MainException::INVALID_DIST_MESSAGE;
                            $code = MainException::INVALID_DIST_CODE;
                            break;

                        case 'deploy':
                            $message = MainException::INVALID_DEPLOY_MESSAGE;
                            $code = MainException::INVALID_DEPLOY_CODE;
                            break;

                    }

                    throw new MainException($message, $code);

                }

            }

        }

        switch ($command) {

            case 'deploy':
                $this->deploy();
                break;

            default:
                echo "\nInvalid command.\n";
                break;

        }

    }

    public function deploy()
    {



    }

}
