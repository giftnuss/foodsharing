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
			->whereInList('id', [37, 38, 47, 48])
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
				'body' => '<img class="img-fluid" src="/img/startpage/hackweekend-img.png">'
			],
			[
			'id' => 79,
			'name' => 'startpage-block1-beta',
			'title' => '-- ignored --',
			'body' => '<h2>Beta-Test-Version</h2>'
			],
			[
				'id' => 80,
				'name' => 'startpage-block2-beta',
				'title' => '-- ignored --',
				'body' => '<p>Hier testen wir Änderungen an der Webseite, bevor diese für
				alle veröffentlicht werden. Bitte melde dich bei der überregionalen AG Beta Testing, um mitzuhelfen.</p>'
			],
			[
				'id' => 81,
				'name' => 'startpage-block3-beta',
				'title' => '-- ignored --',
				'body' => '<img class="img-fluid" src="/img/startpage/hackweekend-img.png">'
			],
			[
				'id' => 82,
				'name' => 'startpage-block1-at',
				'title' => '-- ignored --',
				'body' => '<h2>Neuigkeiten</h2>'
			],
			[
				'id' => 83,
				'name' => 'startpage-block2-at',
				'title' => '-- ignored --',
				'body' => '<h3>Online Hackweekend</h3><p>Sei dabei wenn wir foodsharing gemeinsam besser machen.
				Dieses Wochenende ist für alle - nicht nur für Softwareentwickler*innen,Tester*innen, Designer*innen,
				sondern auch für Leute vom Support, der Öffentlichkeitsarbeit, der Bildungsarbeit etc.</p>
 				<p><a href="https://devblog.foodsharing.de">Erfahre mehr</a></p>'
			],
			[
				'id' => 84,
				'name' => 'startpage-block3-at',
				'title' => '-- ignored --',
				'body' => '<img class="img-fluid" src="/img/startpage/hackweekend-img.png">'
			],
			[
				'id' => 85,
				'name' => 'startpage-block1-ch',
				'title' => '-- ignored --',
				'body' => '<h2>Neuigkeiten</h2>'
			],
			[
				'id' => 86,
				'name' => 'startpage-block2-ch',
				'title' => '-- ignored --',
				'body' => '<h3>Online Hackweekend</h3><p>Sei dabei wenn wir foodsharing gemeinsam besser machen.
				Dieses Wochenende ist für alle - nicht nur für Softwareentwickler*innen,Tester*innen, Designer*innen,
				sondern auch für Leute vom Support, der Öffentlichkeitsarbeit, der Bildungsarbeit etc.</p>
 				<p><a href="https://devblog.foodsharing.de">Erfahre mehr</a></p>'
			],
			[
				'id' => 87,
				'name' => 'startpage-block3-ch',
				'title' => '-- ignored --',
				'body' => '<img class="img-fluid" src="/img/startpage/hackweekend-img.png">'
			]
		];

		$this->table('fs_content')->insert($rows)->save();
	}
}
