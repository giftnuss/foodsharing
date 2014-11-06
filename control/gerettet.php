<?php
$gerettet = $db->getAllGerettet();
addBread('Hochrechnung');

addContent(v_field('<div class="ui-padding">
		<p>Die Lebensmittelretter haben insgesammt ca. <strong>'.number_format($gerettet,2,",",".").' kg</strong> Lebensmittel gerettet</p>'.v_info('Die berechnung erfolgt nur bei Betrieben, die bereits Regelmäßig spenden, d.h. Unregelmäßige / monatliche Abholungen etc. sind nicht mir eingerechnet').'',
		'Hochrechnung lebensmittelretten.de
		
		</div>'));