/**
* @copyright	Copyright (C) 2008 Blue Flame IT (Jersey) Ltd. All rights reserved.
* @license		GNU/GPL v2,
* @link 		http://www.phil-taylor.com
* @author 		Phil Taylor <me@phil-taylor.com> 
*/

JGears = {

	update : function() {
	wait = this.I('gears-wait');
	wait.innerHTML = ('Updating...');
	
	this.message(1);
	},

	createStore : function() {
		if ('undefined' == typeof google || !google.gears)
			return;

		if ('undefined' == typeof localServer)
			localServer = google.gears.factory.create("beta.localserver");

		store = localServer.createManagedStore(this.storeName());
		store.manifestUrl = "../plugins/system/GoogleGears/gears-manifest.php";
		store.enabled = true;
		store.checkForUpdate();
		this.message();
	},

	getPermission : function() {
		if ('undefined' != typeof google && google.gears) {
			if (!google.gears.factory.hasPermission)
				google.gears.factory
						.getPermission(
								'Joomla 1.5.x',
								'../administrator/templates/khepri/images/h_green/j_header_left.png',
								'This site would like to use Google Gears to enable faster Joomla Administration.');

			try {
				this.createStore();
			} catch (e) {
			} // silence if canceled
		}
	},

	storeName : function() {
		var name = window.location.protocol + window.location.host;

		name = name.replace(/[\/\\:*"?<>|;,]+/g, '_'); // gears beta doesn't allow certain chars in the store name
		name = 'J_' + name.substring(0, 60); // max length of name is 64
												// chars

		return name;
	},

	message : function(show) {
		var t = this, msg1 = t.I('gears-msg1'), msg2 = t.I('gears-msg2'), msg3 = t
				.I('gears-msg3'), num = t.I('gears-upd-number'), wait = t
				.I('gears-wait');

		if (!msg1)
			return;

		if ('undefined' != typeof google && google.gears) {
			if (google.gears.factory.hasPermission) {
				msg1.style.display = msg2.style.display = 'none';
				msg3.style.display = 'block';

				if ('undefined' == typeof store)
					t.createStore();

				store.oncomplete = function() {
					wait.innerHTML = (' ' + JGearsL10n.updateCompleted);
				};
				store.onerror = function() {
					wait.innerHTML = (' ' + JGearsL10n.error + ' ' + store.lastErrorMessage);
				};
				store.onprogress = function(e) {
					if (num)
						num.innerHTML = (' ' + e.filesComplete + ' / ' + e.filesTotal);
				};
			} else {
				msg1.style.display = msg3.style.display = 'none';
				msg2.style.display = 'block';
			}
		}

		if (show)
			t.I('gears-info-box').style.display = 'block';
	},

	I : function(id) {
		return document.getElementById(id);
	}
};

( function() {
	if ('undefined' != typeof google && google.gears)
		return;

	var gf = false;
	if ('undefined' != typeof GearsFactory) {
		gf = new GearsFactory();
	} else {
		try {
			gf = new ActiveXObject('Gears.Factory');
			if (factory.getBuildInfo().indexOf('ie_mobile') != -1)
				gf.privateSetGlobalObject(this);
		} catch (e) {
			if (('undefined' != typeof navigator.mimeTypes)
					&& navigator.mimeTypes['application/x-googlegears']) {
				gf = document.createElement("object");
				gf.style.display = "none";
				gf.width = 0;
				gf.height = 0;
				gf.type = "application/x-googlegears";
				document.documentElement.appendChild(gf);
			}
		}
	}

	if (!gf)
		return;
	if ('undefined' == typeof google)
		google = {};
	if (!google.gears)
		google.gears = {
			factory :gf
		};
})();
setTimeout("JGears.I('module-status').innerHTML = '<span><a href=\"javascript:void(0);\" onclick=\"JGears.message(1)\" >Update Gears</a></span>' + JGears.I('module-status').innerHTML;",5000);