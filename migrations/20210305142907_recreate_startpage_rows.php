<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RecreateStartpageRows extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{
		$builder = $this->getQueryBuilder();
		$builder
			->delete('fs_content')
			->where(['id' => 38])
			->where(['id' => 37])
			->where(['id' => 47])
			->where(['id' => 48])
			->execute();

		$rows = [
			[
				'id' => 76,
				'name' => 'startpage-block1-de',
				'title' => '-- ignored --',
				'body' => '<h2>Neuigkeiten</h2>'
			],
			[
				'id' => 77,
				'name' => 'startpage-block2-de',
				'title' => '-- ignored --',
				'body' => '<h3>Online Hackweekend</h3><p>Sei dabei wenn wir foodsharing gemeinsam besser machen.
				Dieses Wochenende ist für alle - nicht nur für Softwareentwickler*innen,Tester*innen, Designer*innen,
				sondern auch für Leute vom Support, der Öffentlichkeitsarbeit, der Bildungsarbeit etc.</p>
 				<p><a href="https://devblog.foodsharing.de">Erfahre mehr</a></p>'
			],
			[
				'id' => 78,
				'name' => 'startpage-block3-de',
				'title' => '-- ignored --',
				'body' => '<img src="/img/MG_87011.png">'
			]
		];

		$this->table('fs_content')->insert($rows)->save();
	}
}
