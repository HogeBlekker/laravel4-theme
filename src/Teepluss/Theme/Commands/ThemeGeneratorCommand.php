<?php namespace Teepluss\Theme\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem as File;

class ThemeGeneratorCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate:theme';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Repository config.
	 *
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Repository $config, File $files)
	{
		$this->config = $config;
		$this->files = $files;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		// The theme is already exists.
		if ($this->files->isDirectory($this->getPath('')))
		{
			return $this->error('Theme "'.$this->getTheme().'" is already exists.');
		}

		// Directories.
		$container = $this->config->get('theme::containerDir');

		$this->makeDir($container['asset'].'/css');
		$this->makeDir($container['asset'].'/js');
		$this->makeDir($container['asset'].'/img');
		$this->makeDir($container['layout']);
		$this->makeDir($container['partial']);
		$this->makeDir($container['view']);
		$this->makeDir($container['widget']);

		// Default layout.
		$layout = $this->config->get('theme::layoutDefault');

		// Make file with template.
		$this->makeFile('layouts/'.$layout.'.blade.php', $this->getTemplate('layout'));
		$this->makeFile('partials/header.blade.php', $this->getTemplate('header'));
		$this->makeFile('partials/footer.blade.php', $this->getTemplate('footer'));

		$this->info('Theme "'.$this->getTheme().'" has been created.');
	}

	/**
	 * Make directory.
	 *
	 * @param  string $directory
	 * @return void
	 */
	protected function makeDir($directory)
	{
		if ( ! $this->files->isDirectory($this->getPath($directory)))
		{
			mkdir($this->getPath($directory), 0777, true);
		}
	}

	/**
	 * Make file.
	 *
	 * @param  string $file
	 * @param  string $template
	 * @return void
	 */
	protected function makeFile($file, $template = null)
	{
		if ( ! $this->files->exists($this->getPath($file)))
		{
			$this->files->put($this->getPath($file), $template);
		}
	}

	/**
	 * Get root writable path.
	 *
	 * @param  string $path
	 * @return string
	 */
	protected function getPath($path)
	{
		$rootPath = $this->option('path');

		return $rootPath.'/'.strtolower($this->getTheme()).'/' . $path;
	}

	/**
	 * Get the theme name.
	 *
	 * @return string
	 */
	protected function getTheme()
	{
		return strtolower($this->argument('name'));
	}

	/**
	 * Get default template.
	 *
	 * @param  string $template
	 * @return string
	 */
	protected function getTemplate($template)
	{
		$path = realpath(__DIR__.'/../templates/'.$template.'.txt');

		return $this->files->get($path);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'Name of the theme to generate.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$path = public_path().'/'.$this->config->get('theme::themeDir');

		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to theme directory.', $path),
		);
	}

}