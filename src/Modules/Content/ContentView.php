<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\View;

class ContentView extends View
{
	public function simple(array $cnt): string
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function partner(array $cnt): string
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function impressum(array $cnt): string
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function about(array $cnt): string
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function joininfo(): string
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1> ' . $this->translator->trans('startpage.join_rules') . ' </h1>
			<h3> ' . $this->translator->trans('startpage.join_welcome') . ' </h3>
			<p> ' . $this->translator->trans('startpage.respect') . ' <br><b>' . $this->translator->trans('startpage.register') . '</b></p>
			<h3> ' . $this->translator->trans('startpage.forstores') . ' </h3>
			<p> ' . $this->translator->trans('startpage.together') . ' </p>'
// the paragraph invites to foodsharing - both individuals and stores
			. $this->v_utils->v_field('
			<div class="reddot">
			<h5><span>1</span>  ' . $this->translator->trans('startpage.honest') . '</h5>
			<p> ' . $this->translator->trans('startpage.telltruth') . '</p>
			<h5><span>2</span>  ' . $this->translator->trans('startpage.followrules_a') . '</h5>
			<p> ' . $this->translator->trans('startpage.followrules_b') . ' ' . $this->translator->trans('startpage.followrules_c') . '</p>
			<p> ' . $this->translator->trans('startpage.notallowed') . '</p>'
// the paragraph states do`s and don`t`s for foodsharing, the next ones talk about how one should interact in the community
			. '<h5><span>3</span> ' . $this->translator->trans('startpage.beresponsible') . '</h5>
			<p>30<span style="white-space:nowrap">&thinsp;</span>% ' . $this->translator->trans('startpage.responsibility') . '</p>
			<h5><span>4</span> ' . $this->translator->trans('startpage.bedependable') . '</h5>
			<p>' . $this->translator->trans('startpage.dependability') . '</p>
			<h5><span>5</span> ' . $this->translator->trans('startpage.makeproposals') . '</h5>
			<p>' . $this->translator->trans('startpage.proposals') . '</p>
			</div>', $this->translator->trans('startpage.etiquette'), ['class' => 'ui-padding']) . '
			<p class="buttons"><br><a href="?page=register" style="font-size:180%;" class="button">' . $this->translator->trans('startpage.registernow') . '</a><br></p>
		</div>
		';
	}
}
