<?php 
//
// Company: Cloudmanic Labs, LLC
// By: Spicer Matthews 
// Email: spicer@cloudmanic.com
// Website: http://cloudmanic.com
// Date: 08/31/2013
//

namespace Cloudmanic\Api\Console;

use Guzzle\Http\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Auth extends Command 
{
	//
	// Configure.
	//
	protected function configure()
	{
		$this->setName('auth:access_token');
		$this->setDescription('Authenticate to the Cloudmanic oAuth server and get an access token.');
	}

	//
	// Run command.
	//
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln("\n");
		$output->writeln('<info>##### Lets Get You An Access Token #####</info>');
		$output->writeln("\n");
		
		// Get the email and password
		$dialog = $this->getHelperSet()->get('dialog');
		$email = $dialog->ask($output, '<question>Enter Your Email: </question>');
		$password = $dialog->ask($output, '<question>Enter Your Password: </question>');
		$client_id = $dialog->ask($output, '<question>Client Id: </question>');
		$client_scope = $dialog->ask($output, '<question>Client Scope: </question>');
		
		// POST the data to the server.
		$client = new Client('https://accounts.cloudmanic.com');
		$request = $client->post('oauth/access_token', null, array(
			'email' => $email,
			'password' => $password,
			'client_id' => $client_id,
			'response_type' => 'token',
			'scope' =>  $client_scope,
			'grant_type' => 'password',
			'plat_type' => 'cli'
		));
		$response = $request->send()->json();

		if($response['status'])
		{
			$access_token = $response['access_token'];
			$output->writeln("\n");
			$output->writeln("Access Token: $access_token");
		} else
		{
			return false;
		} 
		
		// Now get the user.
		$output->writeln("\n");
		$output->writeln('<info>##### Getting The User Accounts #####</info>');
		$q = array(
			'access_token' => $access_token,
			'auto_create' => 0,
			'type' => 'website',
			'format' => 'json'
		);
		$url = 'https://accounts.cloudmanic.com/api/v1/me/profile?' . http_build_query($q);		
		$d = json_decode(file_get_contents($url), TRUE);
		
		// Make sure there were no errors.
		if($d['status'] != 1)
		{
			return false;
		}
		
		// Return user object.
		foreach($d['data']['Accounts'] AS $key => $row)
		{
			$output->writeln($row['AccountsDisplayName'] . ' - ' . $row['AccountsId']);
		}

		$output->writeln("\n");
	}
}

/* End File */