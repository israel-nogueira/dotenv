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
            $_NEW = $FILE;
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
		static public function install($FILE = '.env', $replace = false) {
			// 1. Resolve o caminho absoluto para evitar caminhos duplicados ou relativos errados
			$ENV_FILE = realpath($FILE);

			// 2. Valida se o arquivo realmente existe após o realpath
			if (!$ENV_FILE || !file_exists($ENV_FILE)) {
				throw new \Exception("Arquivo .env não encontrado em: " . ($ENV_FILE ?: $FILE), 1);
			}

			// 3. Parser manual (Substitui a parse_ini_file que estava dando erro de 'undefined')
			$content = file_get_contents($ENV_FILE);
			$lines = explode("\n", str_replace("\r", "", $content));
			
			foreach ($lines as $line) {
				$line = trim($line);
				
				// Pula linhas vazias ou comentários
				if (empty($line) || $line[0] === '#') {
					continue;
				}

				// Divide apenas no primeiro '=' encontrado
				if (strpos($line, '=') !== false) {
					list($key, $value) = explode('=', $line, 2);
					
					$key   = trim($key);
					$value = trim($value, " \t\n\r\0\x0B\""); // Remove espaços e aspas dos valores

					// 4. Define a variável de ambiente (Usa \ para garantir função global)
					// Se $replace for false, não sobrescreve variáveis já existentes no sistema
					if ($replace || getenv($key) === false) {
						\putenv("{$key}={$value}");
						$_ENV[$key] = $value;
						$_SERVER[$key] = $value;
					}
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
	static public function update($KEY = null, $VALUE = null, $FILE = '.env', $replace = false) {
		// 1. Resolve o caminho absoluto
		$ENV_FILE = realpath($FILE);

		if (!$ENV_FILE || !file_exists($ENV_FILE)) {
			throw new \Exception("Arquivo .env não encontrado em: " . ($ENV_FILE ?: $FILE), 1);
		}

		// 2. Lê o arquivo linha por linha para preservar comentários e ordem (opcionalmente)
		// Mas para simplificar e seguir sua lógica de reconstrução total:
		$content = file_get_contents($ENV_FILE);
		$lines = explode("\n", str_replace("\r", "", $content));
		$envData = [];
		$found = false;

		foreach ($lines as $line) {
			$line = trim($line);
			if (empty($line) || $line[0] === '#') continue;

			if (strpos($line, '=') !== false) {
				list($k, $v) = explode('=', $line, 2);
				$envData[trim($k)] = trim($v, " \t\n\r\0\x0B\"");
			}
		}

		// 3. Verifica se a chave existe e atualiza
		if (array_key_exists($KEY, $envData)) {
			$envData[$KEY] = $VALUE;
			$found = true;
		}

		if (!$found) {
			throw new \Exception("env::update() => Não existe a chave " . $KEY, 1);
		}

		// 4. Monta o novo conteúdo do arquivo
		$newContent = [];
		foreach ($envData as $k => $v) {
			$newContent[] = "{$k}=\"{$v}\""; // Adicionamos aspas para evitar erros futuros no parser
		}

		// 5. Salva o arquivo
		if (file_put_contents($ENV_FILE, implode(PHP_EOL, $newContent)) === false) {
			throw new \Exception("Erro ao escrever no arquivo: " . $ENV_FILE);
		}

		// 6. Recarrega as variáveis na memória usando a nova install()
		self::install($ENV_FILE, $replace);
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
		static public function insert($KEY = null, $VALUE = null, $FILE = '.env', $replace = false) {
			// 1. Resolve o caminho absoluto e limpa possíveis duplicatas de diretório
			$ENV_FILE = realpath($FILE) ?: $FILE;

			if (!file_exists($ENV_FILE)) {
				throw new \Exception("Arquivo $ENV_FILE não existe", 1);
			}

			// 2. Parser manual para evitar o erro de "undefined function parse_ini_file"
			$content = file_get_contents($ENV_FILE);
			$lines = explode("\n", str_replace("\r", "", $content));
			$ENV = [];
			
			foreach ($lines as $line) {
				$line = trim($line);
				if (empty($line) || $line[0] === '#') continue;
				if (strpos($line, '=') !== false) {
					list($k, $v) = explode('=', $line, 2);
					$ENV[trim($k)] = trim($v, " \t\n\r\0\x0B\"");
				}
			}

			// 3. Lógica de Inserção/Substituição
			// Se a chave não existe OU (existe e o replace é true)
			if (!isset($ENV[$KEY]) || $replace === true) {
				$ENV[$KEY] = $VALUE;
				
				// Atualiza na memória imediata
				\putenv($KEY . '=' . $VALUE);
				$_ENV[$KEY] = $VALUE;
				$_SERVER[$KEY] = $VALUE;

				// 4. Reconstrói o arquivo com aspas para segurança de tipos e espaços
				$NEW = [];
				foreach ($ENV as $key => $val) {
					$NEW[] = $key . '="' . $val . '"';
				}

				// 5. Grava as alterações de volta no arquivo
				if (file_put_contents($ENV_FILE, implode(PHP_EOL, $NEW)) === false) {
					throw new \Exception("Erro ao gravar no arquivo $ENV_FILE", 1);
				}

				// 6. Recarrega para garantir consistência total
				self::install($ENV_FILE, $replace);
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
		static public function delete($KEY = null, $FILE = '.env') {
			// 1. Resolve o caminho absoluto para evitar erros de diretório duplicado
			$ENV_FILE = realpath($FILE) ?: $FILE;

			if (!file_exists($ENV_FILE)) {
				throw new \Exception("Arquivo $ENV_FILE não existe", 1);
			}

			// 2. Parser manual (mesmo padrão usado nas outras funções)
			$content = file_get_contents($ENV_FILE);
			$lines = explode("\n", str_replace("\r", "", $content));
			$ENV = [];
			
			foreach ($lines as $line) {
				$line = trim($line);
				if (empty($line) || $line[0] === '#') continue;
				if (strpos($line, '=') !== false) {
					list($k, $v) = explode('=', $line, 2);
					$ENV[trim($k)] = trim($v, " \t\n\r\0\x0B\"");
				}
			}

			// 3. Verifica se a chave existe para remover
			if (isset($ENV[$KEY])) {
				unset($ENV[$KEY]);

				// 4. Remove da memória do processo atual
				\putenv($KEY); // No PHP, putenv("CHAVE") sem o "=" remove a variável
				unset($_ENV[$KEY]);
				unset($_SERVER[$KEY]);

				// 5. Reconstrói o arquivo com as chaves restantes
				$NEW = [];
				foreach ($ENV as $key => $val) {
					$NEW[] = $key . '="' . $val . '"';
				}

				// 6. Grava de volta no arquivo
				if (file_put_contents($ENV_FILE, implode(PHP_EOL, $NEW)) === false) {
					throw new \Exception("Erro ao gravar no arquivo $ENV_FILE", 1);
				}
			}
		}


}