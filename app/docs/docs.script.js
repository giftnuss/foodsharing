////Initialize Firebase.
var firepadRef = getExampleRef();
// TODO: Replace above line with:
// var firepadRef = new Firebase('<YOUR FIREBASE URL>');

//// Create CodeMirror (with lineWrapping on).
var codeMirror = CodeMirror(document.getElementById('firepad'), { lineWrapping: true });

// Create a random ID to use as our user ID (we must give this to firepad and FirepadUserList).
var userId = Math.floor(Math.random() * 9999999999).toString();

//// Create Firepad (with rich text features and our desired userId).
var firepad = Firepad.fromCodeMirror(firepadRef, codeMirror,
    { richTextToolbar: true, richTextShortcuts: true, userId: userId});

//// Create FirepadUserList (with our desired userId).
var firepadUserList = FirepadUserList.fromDiv(firepadRef.child('users'),
    document.getElementById('userlist'), userId);

//// Initialize contents.
firepad.on('ready', function() {
  if (firepad.isHistoryEmpty()) {
    firepad.setText('Check out the user list to the left!');
  }
});

function getExampleRef() {
	  // Get hash from end of URL or generate a random one.

	  var ref = new Firebase('https://firepad.firebaseio-demo.com');
	  var hash = window.location.hash.replace(/#/g, '');
	  if (hash) {
	    ref = ref.child(hash);
	  } else {
	    ref = ref.push(); // generate unique location.
	    window.location = window.location + '#' + ref.name(); // add it as a hash to the URL.
	  }

	  if (typeof console !== 'undefined')
	    console.log('Firebase data: ', ref.toString());

	  return ref;
	}