<?php

namespace IsraelNogueira\Dotenv;
use Exception;

class env{

    /*
    |--------------------------------------------------------------------------
    | PATH PADRÃO
    |--------------------------------------------------------------------------
    |
    |   Por padrão inicia na raiz do projeto já com a barra.
    |   Então inicia sempre assim ".env"  ou "app/.env" 
    |
    */
        static private function getPath() {
            return   realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR;
        }

    
    /*
    |--------------------------------------------------------------------------
    | CRIANDO UM NOVO .ENV
    |--------------------------------------------------------------------------
    |
    |   Por padrão inicia na raiz do projeto.
    |
    */
        static public function create($FILE='.env') {
            $_NEW = self::getPath().$FILE;
            if(!file_exists($_NEW)){
                @mkdir(dirname($_NEW),0775,true);
                file_put_contents($_NEW,"");
            }
            return true;
        }

    /*
    |--------------------------------------------------------------------------
    | INSTALANDO UM ARQUIVO
    |--------------------------------------------------------------------------
    |
    |   Basicamente importa as variáveis de um arquivo
    |
    */
        static public function install($FILE='.env',$replace=false) {
            $ENV_FILE   = self::getPath() . $FILE;
            if(!file_exists($ENV_FILE)){throw new Exception("Arquivo  $ENV_FILE não existe", 1);}
            $ENV        = parse_ini_file($ENV_FILE);
            $NOW        = getEnv()??[];
            foreach ($ENV as $key => $line){
                if(empty($NOW[$key]) ||  (isset($NOW[$key]) && $replace==true)){
                    putenv($key.'='.$line);
                }
            }
        }


    /*
    |--------------------------------------------------------------------------
    | ALTERA UMA VARIÁVEL DE UM ARQUIVO
    |--------------------------------------------------------------------------
    |
    |   Após alterar, salva-se o arquivo com a variável alterada
    |
    |
    */
        static public function update($KEY=null,$VALUE=null,$FILE='.env',$replace=false) {
            $ENV_FILE   = self::getPath() . $FILE;
            if(!file_exists($ENV_FILE)){throw new Exception("Arquivo  $ENV_FILE não existe", 1);}
            $ENV = parse_ini_file($ENV_FILE);
            if(isset($ENV[$KEY])){
                $ENV[$KEY]  = $VALUE;
                $NEW        = [];
                foreach ($ENV as $key => $line){
                    $NEW[] = $key.'='.$line.'';
                }
                file_put_contents($ENV_FILE,implode(PHP_EOL,$NEW));
                self::install($FILE,$replace);
            }else{
                throw new Exception("env::update() => Não existe a chave ".$KEY, 1);
            }        
        }

    /*
    |--------------------------------------------------------------------------
    | INSERE UMA VARIÁVEL DE UM ARQUIVO
    |--------------------------------------------------------------------------
    |
    |   Após inserir, salva-se o arquivo com a nova variável
    |
    |
    */
        static public function insert($KEY=null,$VALUE=null,$FILE='.env',$replace=false) {
            $ENV_FILE   = self::getPath() . $FILE;
            if(!file_exists($ENV_FILE)){throw new Exception("Arquivo  $ENV_FILE não existe", 1);}
            $ENV = parse_ini_file($ENV_FILE);
            if(empty($ENV[$KEY]) || ( isset($ENV[$KEY]) && $replace==true) ){
                $ENV[$KEY]  = $VALUE;
                putenv($KEY . '=' . $VALUE);
                $NEW        = [];
                foreach ($ENV as $key => $line){$NEW[] = $key.'="'.$line.'"';}
                file_put_contents($ENV_FILE,implode(PHP_EOL,$NEW) );
                self::install($FILE,$replace);
            }         
        }


    /*
    |--------------------------------------------------------------------------
    | EXCLIUIR UMA VARIÁVEL DE UM ARQUIVO
    |--------------------------------------------------------------------------
    |
    |   Após excluir, salva-se o arquivo sem a variável
    |
    |
    */
    static public function delete($KEY=null,$FILE='.env') {
        $ENV_FILE   = self::getPath() . $FILE;
        if(!file_exists($ENV_FILE)){
            throw new Exception("Arquivo  $ENV_FILE não existe", 1);
        }
        $ENV = parse_ini_file($ENV_FILE);
        if(isset($ENV[$KEY])){
            unset($ENV[$KEY]);
            putenv($KEY);
            $NEW        = [];
            foreach ($ENV as $key => $line){
                $NEW[] = $key.'="'.$line.'"';
            }
            file_put_contents($ENV_FILE,implode(PHP_EOL,$NEW) );
        }         
    }


}