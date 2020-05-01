<?php

namespace IdeHelper\CodeCompletion\Task;

use Cake\Core\App;
use Cake\Filesystem\Folder;
use IdeHelper\Utility\AppPath;
use IdeHelper\Utility\Plugin;

class BehaviorTask implements TaskInterface {

	const TYPE_NAMESPACE = 'Cake\ORM';

	/**
	 * @return string
	 */
	public function type() {
		return static::TYPE_NAMESPACE;
	}

	/**
	 * @return string
	 */
	public function create() {
		$behaviors = $this->collectBehaviors();
		if (!$behaviors) {
			return '';
		}

		$content = $this->build($behaviors);

		$content = <<<TXT
abstract class BehaviorRegistry extends \Cake\Core\ObjectRegistry {

$content

}

TXT;

		return $content;
	}

	/**
	 * @return string[]
	 */
	protected function collectBehaviors() {
		$behaviors = [];

		$folders = array_merge(App::core('ORM/Behavior'), AppPath::get('Model/Behavior'));
		foreach ($folders as $folder) {
			$behaviors = $this->addBehaviors($behaviors, $folder);
		}

		$plugins = (array)Plugin::loaded();
		foreach ($plugins as $plugin) {
			$folders = AppPath::get('Model/Behavior', $plugin);
			foreach ($folders as $folder) {
				$behaviors = $this->addBehaviors($behaviors, $folder, $plugin);
			}
		}

		return $behaviors;
	}

	/**
	 * @param string[] $behaviors
	 * @param string $folder
	 * @param string|null $plugin
	 *
	 * @return string[]
	 */
	protected function addBehaviors(array $behaviors, $folder, $plugin = null) {
		$folderContent = (new Folder($folder))->read(Folder::SORT_NAME, true);

		foreach ($folderContent[1] as $file) {
			preg_match('/^(.+)Behavior\.php$/', $file, $matches);
			if (!$matches) {
				continue;
			}
			$name = $matches[1];
			if ($plugin) {
				$name = $plugin . '.' . $name;
			}

			$className = App::className($name, 'Model/Behavior', 'Behavior');
			if (!$className) {
				continue;
			}

			$behaviors[$name] = $className;
		}

		return $behaviors;
	}

	/**
	 * @param string[] $behaviors
	 *
	 * @return string
	 */
	protected function build(array $behaviors) {
		$result = [];

		foreach ($behaviors as $behavior => $className) {
			list($plugin, $name) = pluginSplit($behavior);

			$template = <<<TXT
	/**
	 * $behavior behavior.
	 *
	 * @var \\$className
	 */
	public \$$name;
TXT;
			$result[] = $template;
		}

		return implode(PHP_EOL . PHP_EOL, $result);
	}

}
