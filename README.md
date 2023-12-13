[![Latest Stable Version](https://poser.pugx.org/israel-nogueira/dotenv/v/stable)](https://packagist.org/packages/israel-nogueira/dotenv) 
[![Total Downloads](https://poser.pugx.org/israel-nogueira/dotenv/downloads)](https://packagist.org/packages/israel-nogueira/dotenv) 
[![Latest Unstable Version](https://poser.pugx.org/israel-nogueira/dotenv/v/unstable)](https://packagist.org/packages/israel-nogueira/dotenv) 
[![License](https://poser.pugx.org/israel-nogueira/dotenv/license)](https://packagist.org/packages/israel-nogueira/dotenv)



# SOBRE A CLASSE
Uma classe tão simples que mal precisa de documentação.

Ela importa arquivos `.env` e gerencia suas variáveis de ambiente.<br>
Podendo acessá-las via `$_SERVER`, `getEnv()` ou `$_ENV`.

# INICIANDO

Instale via composer:
```
$ composer require  israel-nogueira/dotenv
```

E pronto, agora você pode utilizar de forma muito simples
```php
    use IsraelNogueira\Dotenv\env;
    include __DIR__.'/vendor/autoload.php';

    /*
    |--------------------------------------------------------------------------
    | CRIANDO UM NOVO .ENV
    |--------------------------------------------------------------------------
    |
    |   Por padrão inicia na raiz do projeto.
    |
    */
        env::create('.env');
        env::create('app/.env.prod');
        env::create('app/.env.dev');


    /*
    |--------------------------------------------------------------------------
    | INSTALANDO UM ARQUIVO
    |--------------------------------------------------------------------------
    |   Basicamente importa as variáveis de um arquivo
    |
    |   @param1:  Path do arquivo, partindo sempre da raiz do projeto
    |   @param2:  Subscreve as variáveis já carregadas ou não
    |
    */
        env::install('.env',false);
        env::install('app/.env.prod',true);
        env::install('app/.env.dev',true);

    /*
    |--------------------------------------------------------------------------
    | UPDATE OU INSERT EM UM ARQUIVO
    |--------------------------------------------------------------------------
    |   Basicamente altera e salva as variáveis no arquivo
    |
    |   @param1:  Chave a ser inserida ou alterada
    |   @param2:  Valor a ser inserido ou alterado
    |   @param3:  Subscreve a variavel já carregada
    |
    */
        env::update('SENHA','123456','.env',false);
        env::insert('SENHA2','123456','.env2',false);

    /*
    |--------------------------------------------------------------------------
    | EXCLUI UMA VARIÁVEL DE UM ARQUIVO
    |--------------------------------------------------------------------------
    |   Basicamente altera e salva as variáveis no arquivo
    |
    |   @param1:  Chave a ser excluida
    |   @param2:  Path do arquivo
    |
    */
        env::delete('SENHA','.env');
        env::delete('SENHA2','.env2');



```

