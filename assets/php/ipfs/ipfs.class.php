<?php

// https://docs.ipfs.io/reference/http/api/

namespace eth_sign;

class IPFS_Server {
    private $IP;
    private $port;
    private $API_port;
    private $https;

    public function __construct($ip, $port, $api, $https) {
        $this->IP = $ip;
        $this->port = $port;
        $this->API_port = $api;
        $this->https = $https;
    }

    public function getURL($uri, $api) {
        $url = 'http' . ($this->https ? 's' : '') . '://' . $this->IP . ':' . ($api ? $this->API_port : $this->port) . '/';
        if ($api) {
            $url .= 'api/v0/';
        }
		//error_log($url . $uri);
        return $url . $uri;
    }
}

class IPFS {
    private $server;
    public function __construct($ip = 'ipfs.theimmutable.net.vhosts', $port = "8088", $api = "5001", $https = false) {
        $this->server = new IPFS_Server($ip, $port, $api, $https);
		//error_log($ip);
    }

    private function send($uri, $api = false, $data = null, $folder = '', $makeDir = false) {
        $url = $this->server->getURL($uri, $api);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if($makeDir) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Disposition: form-data; name="file"; filename="' . $folder . '"',
                'Content-Type: application/x-directory'
            ));
        }

        if ($data !== null) {
            if (!is_array($data)) {
                $data = [$data];
            }
            $post_fields = [];
            foreach ($data as $offset => $filepath) {
                $filedest = ($folder != '' ? $folder . '%2F' : '') . basename($filepath);
				$filedest = urlencode($filedest);
                $cfile = curl_file_create($filepath, 'application/octet-stream', $filedest);
                $post_fields['file' . sprintf('%03d', $offset + 1)] = $cfile;
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }

        $output = curl_exec($ch);
			error_log("\n\n###\n");
			error_log(print_r($output, true));
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $code_category = substr($response_code, 0, 1);
        if ($code_category == '5' || $code_category == '4') {
            $dataOut = @json_decode($output, true);
            if (!$dataOut && json_last_error() != JSON_ERROR_NONE) {
                #throw new \Exception("IPFS code retour $response_code : " . substr($output, 0, 200), $response_code);
				error_log("IPFS code retour $response_code : " . substr($output, 0, 200), $response_code);
            } else if (is_array($dataOut) && isset($dataOut['Code']) && isset($dataOut['Message'])) {
                #throw new \Exception("IPFS Erreur {$dataOut['Code']} : {$dataOut['Message']}", $response_code);
				error_log("IPFS Erreur {$dataOut['Code']} : {$dataOut['Message']}", $response_code);
            }
        }

        curl_close($ch);
        if ($output === false) {
            error_log('IPFS ne répond pas...');
        } else if ($api) {
            $output = json_decode($output, true);
        }
        return $output;
    }

    private function sendFile($uri, $api = false, $filepath = null, $folder = '', $options = null, $multiple = false) {
        $query_string = $options!=null ? '?' . http_build_query($options) : '';
        $response = $this->send($uri . $query_string, $api, $filepath, $folder);
        if ($multiple) {
            $data = [];
            foreach (explode("\n", $response) as $json_data_line) {
                if (strlen(trim($json_data_line))) {
                    $data[] = json_decode($json_data_line, true);
                }
            }
        } else {
            $data = $response;
        }
        return $data;
    }

	/**
	* Retrieves the contents of a hash
	*
	* @param string $hash of the content to retrieve
	* @return string the corresponding content
	*/
    public function cat($hash) {
        return $this->send('ipfs/' . $hash);
    }

	/**
	* Add text content to IPFS
	*
    * @param boolean $content text content sent
	* @return string the hash of the sent content
	*/
    public function addContent($content) {
        $req = $this->send('add?stream-channels=true', true, $content);
        return $req['Hash'];
    }

	/**
	* Adds folder directly to IPFS
	*
	* @param string $folder name of folder to create
	* @return array returns an array of the form:
	* {
	* "Name": "folder name",
	* "Hash": "hash returned by IPFS"
	* }
	*/
    public function makeFolder($folder) {
        return $this->send('add', true, null, $folder, null, true);
    }

	/**
	* Adds a file directly to IPFS
	*
	* @param string $filepath local path of file to send
	* @param string $folder existing IPFS folder to write file to
	* @param boolean $pin pin file if true
	* @return array returns an array of the form:
	* {
	* "Name": "file name",
	* "Hash": "hash returned by IPFS",
	* "Size": "file size"
	* }
	*/
    public function addFile($filepath, $folder = '', $pin = true) {
        $options = [
            'pin' => $pin
        ];
        return $this->sendFile('add', true, $filepath, $folder, $options);
    }

	/**
	* Deletes a file
	*
	* @param string $filename filename
	* @param string $folder name of the folder containing the file
	* @return array returns an http response of type text/plain
	*/
    public function removeFile($filename, $folder = '') {
        $uri = 'files/rm?arg=' . ($folder != '' ? $folder . '%2F' : '') . $filename;
        return $this->send($uri, true);
    }
	/**
	* Copy a file into a Folder
	*
	* @param string $filename filename
	* @param string $destfilename name of the folder containing the file
	* @return array returns an http response of type text/plain
	*/
    public function cpFile($filename, $destfilename = '') {
        $uri = 'files/cp?arg='.$filename.'&arg='.$destfilename;
		#error_log("\n\n$uri");
        return $this->send($uri, true);
    }
	/**
	* Move a file into a Folder
	*
	* @param string $filename filename
	* @param string $destfilename name of the folder containing the file
	* @return array returns an http response of type text/plain
	*/
    public function mvFile($filename, $destfilename = '') {
        $uri = 'files/mv?arg='.$filename.'&arg='.$destfilename;
		#error_log("\n\n$uri");
        return $this->send($uri, true);
    }
	/**
	* Return file status
	*
	* @param string $path
	*/
    public function stat($path = '') {
        $uri = 'files/stat?arg='.$path;
        return $this->send($uri, true);
    }

	/**
	* Adds file(s) in a folder to IPFS
	*
	* @param string|array $filepaths path of file to send or array if several
	* @param boolean $pin pin file if true
	* @return array returns a structure of the form:
	* {
	* 	"files": [
	* 		{
	* 			"Name": "file1.ex1",
	* 			"Hash": "...hash_value_1..."
	* 		},
	* 		{
	* 			"Name": "file2.ex2",
	* 			"Hash": "...hash_value_2..."
	* 		}
	* 		],
	* 	"FolderHash": "...hash_of_the_parent_folder..."
	* }
	*/
    public function addFiles($filepaths, $pin = true) {
        $options = [
            'pin' => $pin,
            'w' => 'true'
        ];
        $responses = $this->sendFile('add', true, $filepaths, $options, true);
        $return_data = [
            'files' => [],
            'FolderHash' => '',
        ];
        $responses_count = count($responses);
        for ($i = 0; $i < $responses_count; $i++) {
            if ($i === $responses_count - 1) {
                $return_data['FolderHash'] = $responses[$i]['Hash'];
                continue;
            }
            $return_data['files'][$i] = $responses[$i];
        }
        return $return_data;
    }

	/**
	* Gets hash-bound structure
	*
	* @param string $hash of node to inspect
	* @return array returns an array of structures of the form:
	* [
	* ['Hash', 'Size', 'Name'],
	* ...
	* ]
	*/
    public function ls($hash) {
        $data = $this->send('ls/' . $hash, true);
        return $data['Objects'][0]['Links'];
    }

    /**
     * Retourne la taille d'un noeud
     * 
     * @param string $hash du noeud à inspecter
     * @return int taille du noeud
     */
    public function size($hash) {
        $data = $this->send('object/stat/' . $hash, true);
        return $data['CumulativeSize'];
    }

    /**
     * Epingle un noeud
     * 
     * @param string $hash du noeud à épingler
     * @return boolean true si épinglé
     */
    public function pinAdd($hash) {
        return $this->send('pin/add/' . $hash, true);
    }

    /**
     * Supprime l'épingle d'un noeud
     * 
     * @param string $hash du noeud où supprimer l'épingle
     * @return boolean true si noeud épingle supprimée
     */
    public function pinRm($hash) {
        return $this->send('pin/rm/' . $hash, true);
    }

    /**
     * Retourne la version IPFS utilisée
     * 
     * @return string version IPFS
     */
    public function version() {
        $data = $this->send('version', true);
        return $data['Version'];
    }

    /**
     * Retourne l'identifiant utilisé
     * 
     * @return string identifiant client IPFS
     */
    public function id() {
        return $this->send('id', true);
    }
}
