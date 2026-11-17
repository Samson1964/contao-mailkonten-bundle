/**
 * Mailkonten für Contao Open Source CMS
 *
 * Skript für die Knöpfe im Formular:
 *   - „Server abrufen“ füllt die Felder für SMTP-, POP3- und IMAP-Server samt Ports.
 *   - „Kopieren“ legt die Kopiervorlage in die Zwischenablage.
 *
 * Die Knöpfe tragen data-mailkonten-Kennungen; das Skript hängt sich per
 * Ereignisweitergabe ans Dokument. So funktionieren auch Knöpfe, die erst später
 * per Ajax ins Formular kommen (die Unterpalette POP3/IMAP).
 *
 * @license LGPL-3.0-or-later
 */
(function () {
	'use strict';

	if (window.mailkontenGeladen) {
		return;
	}

	window.mailkontenGeladen = true;

	/** Felder, die „Server abrufen“ füllen darf. */
	var FELDER = ['smtp_server', 'smtp_port', 'imap_server', 'imap_port', 'pop3_server', 'pop3_port'];

	/**
	 * Schreibt eine Rückmeldung neben den Knopf.
	 *
	 * @param {Element} knopf  Der angeklickte Knopf
	 * @param {string}  text   Meldung; textContent, also nie als HTML gedeutet
	 * @param {boolean} fehler true färbt die Meldung als Fehler
	 */
	function melde(knopf, text, fehler) {
		var ort = knopf.parentNode.querySelector('[data-mailkonten="meldung"]');

		if (!ort) {
			return;
		}

		ort.textContent = text;
		ort.style.color = fehler ? '#c33' : '#080';
	}

	/**
	 * Baut die Adresse des Abrufs.
	 *
	 * Der Pfad der aktuellen Seite wird übernommen, damit es unter Contao 4.13
	 * und 5 sowie bei geändertem Backend-Pfad stimmt.
	 *
	 * @param {string} email Eingetippte Adresse
	 * @returns {string}
	 */
	function abrufAdresse(email) {
		return window.location.pathname + '?do=mailkonten&key=server&email=' + encodeURIComponent(email);
	}

	/** Klick auf „Server abrufen“. */
	function serverAbrufen(knopf) {
		var feld = document.getElementById('ctrl_email');
		var email = feld ? feld.value.trim() : '';

		if (!email) {
			melde(knopf, 'Bitte zuerst eine E-Mail-Adresse eintragen.', true);
			return;
		}

		knopf.disabled = true;
		melde(knopf, 'Wird abgerufen …', false);

		fetch(abrufAdresse(email), {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
		}).then(function (antwort) {
			return antwort.json();
		}).then(function (daten) {
			if (daten.ok && daten.werte) {
				FELDER.forEach(function (name) {
					var ziel = document.getElementById('ctrl_' + name);

					if (ziel && Object.prototype.hasOwnProperty.call(daten.werte, name)) {
						ziel.value = daten.werte[name];
					}
				});
			}

			melde(knopf, daten.meldung || 'Keine Antwort.', !daten.ok);
		}).catch(function () {
			melde(knopf, 'Der Abruf ist fehlgeschlagen.', true);
		}).then(function () {
			knopf.disabled = false;
		});
	}

	/** Klick auf „Kopieren“. */
	function kopieren(knopf) {
		var kasten = knopf.closest('[data-mailkonten="kopiervorlage"]');
		var text = kasten ? kasten.querySelector('[data-mailkonten="text"]') : null;

		if (!text) {
			return;
		}

		var fertig = function () { melde(knopf, 'Kopiert.', false); };
		var fehlgeschlagen = function () { melde(knopf, 'Kopieren nicht möglich – Text bitte von Hand markieren.', true); };

		// Die Zwischenablage-Schnittstelle gibt es nur auf https-Seiten (und localhost);
		// auf anderen greift der alte Weg über das Markieren und Kopieren
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(text.value).then(fertig, fehlgeschlagen);
			return;
		}

		text.focus();
		text.select();

		try {
			(document.execCommand('copy') ? fertig : fehlgeschlagen)();
		} catch (e) {
			fehlgeschlagen();
		}
	}

	document.addEventListener('click', function (ereignis) {
		var knopf = ereignis.target.closest ? ereignis.target.closest('[data-mailkonten]') : null;

		if (!knopf) {
			return;
		}

		switch (knopf.getAttribute('data-mailkonten')) {
			case 'server-abrufen':
				ereignis.preventDefault();
				serverAbrufen(knopf);
				break;
			case 'kopieren':
				ereignis.preventDefault();
				kopieren(knopf);
				break;
		}
	});
})();
