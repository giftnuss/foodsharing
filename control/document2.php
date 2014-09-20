<?php

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_document'),'?page=document');
	addBread(s('bread_new_document'));
			
	addContent(document_form());

	addContent(v_field(v_menu(array(
		pageLink('document','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_document($id))
	{
		info(s('document_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_document'),'?page=document');
	addBread(s('bread_edit_document'));
	
	$data = $db->getOne_document($id);
	setEditData($data);
			
	addContent(document_form());
			
	addContent(v_field(v_menu(array(
		pageLink('document','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
else if(isset($_GET['id']))
{
	go('?page=listDocument&id='.(int)$_GET['id']);
}
else
{
	addBread(s('document_bread'),'?page=document');
	
	if(isOrgaTeam())
	{
	
		if($data = $db->getBasics_document())
		{
			$rows = array();
			foreach ($data as $d)
			{
						
				$rows[] = array(
					array('cnt' => '<a class="linkrow ui-corner-all" href="?page=document&id='.$d['id'].'&a=edit">'.$d['name'].'</a>'),
					array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
				));
			}
			
			$table = v_tablesorter(array(
				array('name' => s('name')),
				array('name' => s('actions'),'sort' => false,'width' => 50)
			),$rows);
			
			addContent(v_field($table,'Alle Dokumente'));	
		}
		else
		{
			info(s('document_empty'));		
		}
		addContent(v_field(v_menu(array(
			array('href' => '?page=document&a=neu','name' => s('neu_document'))
		)),'Aktionen'),CNT_RIGHT);

	}
	else
	{
		goPage('listDocument');
	}
	
}					
function document_form()
{
	global $db;
	global $g_data;
	
	$user = $db->getValues(array('id','name','nachname','photo'), 'foodsaver', fsId());
	addScript('https://cdn.firebase.com/v0/firebase.js');
	addScript('js/firepad/codemirror/lib/codemirror.js');
	addCss('js/firepad/codemirror/lib/codemirror.css');
	addScript('js/firepad/firepad.js');
	addCss('js/firepad/firepad.css');
	//addScript('js/firepad/firepad-userlist.js');
	addCss('js/firepad/firepad-userlist.css');
	
	addJs('
	var FirepadUserList = (function() {
  function FirepadUserList(ref, place, userId) {
    if (!(this instanceof FirepadUserList)) { return new FirepadUserList(ref, place, userId); }

    this.ref_ = ref;
    this.userId_ = userId;
    //this.userId_ = 10;
    this.place_ = place;
    this.firebaseCallbacks_ = [];

    var self = this;
    this.displayName_ = \'Guest \' + Math.floor(Math.random() * 1000);
    //this.displayName_ = \''.jsSafe($user['name']).' \' + this.userId;
    this.firebaseOn_(ref.root().child(\'.info/connected\'), \'value\', function(s) {
      if (s.val() === true && self.displayName_) {
        var nameRef = ref.child(self.userId_).child(\'name\');
        nameRef.onDisconnect().remove();
        nameRef.set(self.displayName_);
      }
    });

    this.userList_ = this.makeUserList_()
    place.appendChild(this.userList_);
  }

  // This is the primary "constructor" for symmetry with Firepad.
  FirepadUserList.fromDiv = FirepadUserList;

  FirepadUserList.prototype.dispose = function() {
    this.removeFirebaseCallbacks_();
    this.ref_.child(this.userId_).child(\'name\').remove();

    this.place_.removeChild(this.userList_);
  };

  FirepadUserList.prototype.makeUserList_ = function() {
    return elt(\'div\', [
      this.makeHeading_(),
      elt(\'div\', [
        this.makeUserEntryForSelf_(),
        this.makeUserEntriesForOthers_()
      ], {\'class\': \'firepad-userlist-users\' })
    ], {\'class\': \'firepad-userlist\' });
  };

  FirepadUserList.prototype.makeHeading_ = function() {
    var counterSpan = elt(\'span\', \'0\');
    this.firebaseOn_(this.ref_, \'value\', function(usersSnapshot) {

    	$(\'#fp-user-online\').html(\'(\'+usersSnapshot.numChildren()+\')\');

    });

    return elt(\'div\');
  };

  FirepadUserList.prototype.makeUserEntryForSelf_ = function() {
    var myUserRef = this.ref_.child(this.userId_);

    var colorDiv = elt(\'div\', null, { \'class\': \'firepad-userlist-color-indicator\' });
    this.firebaseOn_(myUserRef.child(\'color\'), \'value\', function(colorSnapshot) {
      var color = colorSnapshot.val();
      if (typeof color === \'string\' && color.match(/^#[a-fA-F0-9]{3,6}$/)) {
        colorDiv.style.backgroundColor = color;
      }
    });

    var nameInput = elt(\'a\', this.displayName_+\'\', { \'class\': \'firepad-userlist-name\',"href":"#","onclick":"profile('.(int)$user['id'].');return false;"} );
    //nameInput.value = this.displayName_;

    var nameHint = elt(\'div\', \'\' );
    var nameDiv = elt(\'div\', [nameInput, nameHint]);

    return elt(\'div\', [ colorDiv, nameDiv ], { \'class\': \'firepad-userlist-user\' });
  };

  FirepadUserList.prototype.makeUserEntriesForOthers_ = function() {
    var self = this;
    var userList = elt(\'div\');
    var userId2Element = { };

    function updateChild(userSnapshot, prevChildName) {
      var userId = userSnapshot.name();
      var div = userId2Element[userId];
      if (div) {
        userList.removeChild(div);
        delete userId2Element[userId];
      }
      var name = userSnapshot.child(\'name\').val();
      if (typeof name !== \'string\') { name = \'Guest\'; }
      name = name.substring(0, 20);

      var color = userSnapshot.child(\'color\').val();
      if (typeof color !== \'string\' || !color.match(/^#[a-fA-F0-9]{3,6}$/)) {
        color = "#ffb"
      }

      var colorDiv = elt(\'div\', null, { \'class\': \'firepad-userlist-color-indicator\' });
      colorDiv.style.backgroundColor = color;

      var nameDiv = elt(\'div\', name || \'Guest\', { \'class\': \'firepad-userlist-name\' });

      var userDiv = elt(\'div\', [ colorDiv, nameDiv ], { \'class\': \'firepad-userlist-user\' });
      userId2Element[userId] = userDiv;

      if (userId === self.userId_) {
        // HACK: We go ahead and insert ourself in the DOM, so we can easily order other users against it.
        // But don\'t show it.
        userDiv.style.display = \'none\';
      }

      var nextElement =  prevChildName ? userId2Element[prevChildName].nextSibling : userList.firstChild;
      userList.insertBefore(userDiv, nextElement);
    }

    this.firebaseOn_(this.ref_, \'child_added\', updateChild);
    this.firebaseOn_(this.ref_, \'child_changed\', updateChild);
    this.firebaseOn_(this.ref_, \'child_moved\', updateChild);
    this.firebaseOn_(this.ref_, \'child_removed\', function(removedSnapshot) {
      var userId = removedSnapshot.name();
      var div = userId2Element[userId];
      if (div) {
        userList.removeChild(div);
        delete userId2Element[userId];
      }
    });

    return userList;
  };

  FirepadUserList.prototype.firebaseOn_ = function(ref, eventType, callback, context) {
    this.firebaseCallbacks_.push({ref: ref, eventType: eventType, callback: callback, context: context });
    ref.on(eventType, callback, context);
    return callback;
  };

  FirepadUserList.prototype.firebaseOff_ = function(ref, eventType, callback, context) {
    ref.off(eventType, callback, context);
    for(var i = 0; i < this.firebaseCallbacks_.length; i++) {
      var l = this.firebaseCallbacks_[i];
      if (l.ref === ref && l.eventType === eventType && l.callback === callback && l.context === context) {
        this.firebaseCallbacks_.splice(i, 1);
        break;
      }
    }
  };

  FirepadUserList.prototype.removeFirebaseCallbacks_ = function() {
    for(var i = 0; i < this.firebaseCallbacks_.length; i++) {
      var l = this.firebaseCallbacks_[i];
      l.ref.off(l.eventType, l.callback, l.context);
    }
    this.firebaseCallbacks_ = [];
  };


  /** DOM helpers */
  function elt(tag, content, attrs) {
    var e = document.createElement(tag);
    if (typeof content === "string") {
      setTextContent(e, content);
    } else if (content) {
      for (var i = 0; i < content.length; ++i) { e.appendChild(content[i]); }
    }
    for(var attr in (attrs || { })) {
      e.setAttribute(attr, attrs[attr]);
    }
    return e;
  }

  function setTextContent(e, str) {
    e.innerHTML = "";
    e.appendChild(document.createTextNode(str));
  }

  function on(emitter, type, f) {
    if (emitter.addEventListener) {
      emitter.addEventListener(type, f, false);
    } else if (emitter.attachEvent) {
      emitter.attachEvent("on" + type, f);
    }
  }

  function off(emitter, type, f) {
    if (emitter.removeEventListener) {
      emitter.removeEventListener(type, f, false);
    } else if (emitter.detachEvent) {
      emitter.detachEvent("on" + type, f);
    }
  }

  function preventDefault(e) {
    if (e.preventDefault) {
      e.preventDefault();
    } else {
      e.returnValue = false;
    }
  }

  function stopPropagation(e) {
    if (e.stopPropagation) {
      e.stopPropagation();
    } else {
      e.cancelBubble = true;
    }
  }

  function stopEvent(e) {
    preventDefault(e);
    stopPropagation(e);
  }

  return FirepadUserList;
})();
			
			
	function getExampleRef() {		
		  var ref = new Firebase("https://glaring-fire-3689.firebaseio.com/firepads/'.(int)$_GET['id'].'");
		  var hash = window.location.hash.replace(/#/g, "");
		  if (hash) {
		    ref = ref.child(hash);
		  } else {
		    ref = ref.push(); // generate unique location.
		    window.location = window.location + "#" + ref.name(); // add it as a hash to the URL.
		  }
		
		  if (typeof console !== "undefined")
		    console.log("Firebase data: ", ref.toString());
		
		  return ref;
	}	
	var firepadRef = getExampleRef();

    var codeMirror = CodeMirror(document.getElementById("firepad"), { lineWrapping: true });

    var userId = Math.floor(Math.random() * 9999999999).toString();
	
    var firepad = Firepad.fromCodeMirror(firepadRef, codeMirror,
        { richTextToolbar: true, richTextShortcuts: true, userId: userId});

    var firepadUserList = FirepadUserList.fromDiv(
    	firepadRef.child("users"),
        document.getElementById("userlist"), 
        userId
    );


    firepad.on("ready", function() {
      if (firepad.isHistoryEmpty()) {
        
      }
    });	
	');
	
	addContent(v_field('<div id="userlist"></div>','Benutzer Online <span id="fp-user-online"></span>',array('class'=>'ui-padding')),CNT_RIGHT);
	return v_form('test', array(
			
			v_field('<div id="firepad"></div>','Collaborative Texteingabe'),
			v_field(
			v_form_text('name',array('required'=>true)).
			v_form_file('file').
			v_form_select('rolle',array('values'=>array(
			array('id'=> 0, 'name' => 'Alle'),
			array('id'=> 1, 'name' => 'Foodsaver'),
			array('id'=> 2, 'name' => 'Botschafter'),
			array('id'=> 3, 'name' => 'Orga-Team')
			),'required'=>true)),
			
			'Dokument',
			array('class'=>'ui-padding')
			
			)
			//v_field(v_form_tinymce('body',array('nowrapper'=>true)), 'Direkte Texteingabe')
	));
	
	
	return v_quickform('document',array(
		
		v_form_text('name'),
		v_form_file('file'),
		//v_form_tinymce('body'),
		'<div id="firepad"></div>',
		v_form_select('rolle',array('values'=>array(
			array('id'=> 0, 'name' => 'Alle'),
			array('id'=> 1, 'name' => 'Foodsaver'),
			array('id'=> 2, 'name' => 'Botschafter'),
			array('id'=> 3, 'name' => 'Orga-Team')
		)))	
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($file = handleAttach('file'))
		{
			$g_data['file'] = json_encode($file);
		}
		else
		{
			$g_data['file'] = $db->getVal('file', 'document', (int)$_GET['id']);
		}
		
		if($db->update_document($_GET['id'],$g_data))
		{
			info(s('document_edit_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
function handle_add()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['file'] = json_encode(handleAttach('file'));
		if($db->add_document($g_data))
		{
			info(s('document_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>