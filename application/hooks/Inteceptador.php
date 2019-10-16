<?php

/**
 * @package     Gerens/CodeIgniter
 * @subpackage	Hooks
 * @category	Hooks
 * @author      João Vinezof
 * 
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 
 * Classe Interceptadora, recebe todas as requisições e 
 * 
 * @package     Gerens/CodeIgniter
 * @subpackage	Hooks
 * @category	Hooks
 * @author      João Vinezof
 * 
 */
class Interceptador extends MY_Hooks
{

	/**
	 * @var Url $uri helper url
	 */
	protected $uri;

	/**
	 * @var bool Interceptador::DEBUG = false se for true o handler é ignorado
	 */
	const DEBUG = false;

	/**
	 * @var string[] $RECURSOS_BLOQUEADOS lista com uri bloqueados
	 */
	private $RECURSOS_BLOQUEADOS = array();

	/**
	 * @var string[] $RECURSOS_BLOQUEADOS lista com uri livres
	 */
	private $RECURSOS_LIVRES = array("admin/login", "admin/logar", "admin/cadastro");


	public function __construct()
	{
		parent::__construct();

		$CI = &get_instance();
		$this->uri = $CI->uri;
	}

	public function preHandler()
	{

		// Se $DEBUG for true ignoramos a função
		if (Interceptador::DEBUG === true) {
			return;
		}

		// Toda parte administrativa inicia-se com admin
		// Então vamos verificar se admin comparando a primeira parte
		if ($this->uri->segmet(1) != "admin") {

			// Saindo do preHandler
			return;
		}

		// Variavel que vai definir se o recurso é livre
		// Se o recurso for livre a pagina é liberada sem nenhuma interferencia
		$liberarAcesso = false;

		// Laço que vai pecorrer os recursos livres para saber se o usuario tem acesso
		foreach ($this->RECURSOS_LIVRES as $recursoLivre) {

			// Se o recurso for livre liberamos o acesso
			// Se não for bloqueamos o mesmo
			if ($this->uri->uri_string() == $recursoLivre) {
				$liberarAcesso = true;
				break;
			}
		}

		// Se o recurso for livre a pagina é liberada sem nenhuma ação
		// Se o recurso não for livre verificamos se o usario está logado
		if (!$liberarAcesso) {

			// Verificando se o usuario estar offline
			// Se a sessão não existir é porque ele estar offline
			if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] == null) {

				/*
                 *  Usuario offline bloqueamos o acesso
                 */

				// Aviso de erro
				$alerta = array(
					"toast" => true,
					"type" => "warning",
					"title" => "Você está desconectado ou sua sessão expirou",
					"position" => "top",
					"showConfirmButton" => false,
					"timer" => 300
				);

				redirect(base_url("admin/login?voltar=" . base64_encode($this->uri->uri_string())));
			}
		}
	}
}
