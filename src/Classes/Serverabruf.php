<?php

declare(strict_types=1);

/**
 * Mailkonten für Contao Open Source CMS
 *
 * @author    Frank Hoppe
 * @license   LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoMailkontenBundle\Classes;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Backend-Schnittstelle des Knopfes „Server abrufen“.
 *
 * Die Klasse hängt als Modulschlüssel „server“ am Backend-Modul (siehe
 * Resources/contao/config/config.php). Das Skript hinter dem Knopf ruft
 * `?do=mailkonten&key=server&email=…` auf und bekommt die Antwort als JSON.
 *
 * Der Weg über einen Modulschlüssel statt über eine eigene Route hat einen
 * Grund: Contao prüft dort selbst, ob der angemeldete Benutzer das Modul
 * „Mailkonten“ benutzen darf. Eine eigene Route müsste diese Prüfung nachbauen.
 */
final class Serverabruf
{
	/**
	 * Fragt Server und Ports zu der übergebenen Adresse ab und antwortet mit JSON.
	 *
	 * Die Antwort wird als Ausnahme geworfen, nicht zurückgegeben. Contao fängt
	 * die ResponseException ab und liefert die Antwort unverändert aus — sonst
	 * würde das JSON in die Backend-Seite eingebettet.
	 *
	 * @param DataContainer $dc Data Container der Liste; wird nicht ausgewertet, gehört
	 *                          aber zur von Contao erwarteten Signatur
	 *
	 * @return string Immer der leere String; der eigentliche Rückgabeweg ist die Ausnahme.
	 *                Der leere String greift nur, wenn die Methode ohne den Schlüssel „server“
	 *                aufgerufen wurde
	 *
	 * @throws ResponseException Trägt die JSON-Antwort mit den Schlüsseln ok, meldung und werte
	 */
	public function abrufen(DataContainer $dc): string
	{
		if ('server' !== Input::get('key')) {
			return '';
		}

		/** @var Serversuche $suche */
		$suche = System::getContainer()->get(Serversuche::class);

		$antwort = new JsonResponse($suche->ermittle((string) Input::get('email')));
		$antwort->headers->set('Cache-Control', 'no-store');

		throw new ResponseException($antwort);
	}
}
