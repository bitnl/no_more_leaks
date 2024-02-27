# No More Leaks

Roundcube plugin voor het [No More
Leaks](https://www.politie.nl/onderwerpen/no-more-leaks.html) project.

No More Leaks is een project van de Nederlandse politie waar ze geanonimiseerde
data over gelekte inloggegevens deelt met private partners om misbruik van deze
gelekte gegevens te voorkomen.

Voor toegang tot deze data over gelekte inloggegevens dient er een convenant of
toetredingsbepaling getekend te worden met de Nederlandse politie. De Nederlande
politie voorziet in instructies om de gegevens te verkrijgen.

Het is aan de gebruiker van deze Roundcube plugin en deelnemer aan het project
om deze gegevens op zo een manier beschikbaar te maken en up-to-date te houden
dat deze Roundcube plugin er doorheen kan zoeken om gelekte gegevens te detecteren.

## Installatie van de plugin
Plaats de inhoud van dit Git repository, of deze tar-ball, in zijn geheel in een
map genaamd `no_more_leaks` binnen de `plugins/` map van uw Roundcube installatie.

Stel, uw Roundcube installatie is geplaatst in `/opt/roundcube/`:
```
user@webserver:~$ cd /opt/roundcube/plugins/
user@webserver:/opt/roundcube/plugins$ git clone https://github.com/bitnl/no_more_leaks.git
```

Ga de `no_more_leaks` plugin directory in en kopieer vervolgens de configuratie
file `config.inc.php.ex` naar `config.inc.php`:
```
user@webserver:/opt/roundcube/plugins$ cd no_more_leaks
user@webserver: .. gins/no_more_leaks$ cp config.inc.php.ex config.inc.php
```

Pas de inhoud van `config.inc.php` aan met uw favorite tekst-editor en wijzig de
diverse instellingen van de plugin naar uw wens:
```
user@webserver: .. gins/no_more_leaks$ vim config.inc.php
```

### Configuratie van de plugin
Het `config.inc.php`-bestand bevat een aantal opties die van belang zijn voor de werking van de plugin, alsmede het gedrag van de plugin naar de gebruiker toe.

Een aantal instellingen zijn boolean: `true` of `false`, deze waardes worden *zonder* quotes geschreven.

| optie | beschrijving |
| :---: | ------------ |
| `nml_enabled` | Een van `true` of `false`. Wanneer niet `true` is de plugin uitgeschakeld. |
| `nml_debug` | Een van `true` of `false`. Wanneer `true` zal de plugin uitgebreidere informatie schrijven naar de in Roundcube geconfigureerde `log_driver` (zie hiertoe Roundcube's eigen `config.inc.php` file). |
| `nml_invalidate_session_when_leaked` | Een van `true` of `false`. Wanneer `true` zal de gebruiker géén gebruik kunnen maken van Roundcube Webmail als de logingegevens als gelekt zijn bestempeld. Wanneer `false` zou de gebruiker alsnog kunnen doorklikken naar de `/?_task=mail`-URL van Roundcube Webmail om bij zijn of haar e-mails te komen. |
| `nml_redirect_when_leaked` | Een van `true` of `false`. Wanneer `true` zal de gebruiker, wanneer de logingegevens als gelekt zijn bestempeld, worden doorverwezen naar de URL die is ingesteld bij de `nml_redirect_destination`. |
| `nml_redirect_destination` | Een volledige URL naar een pagina waar de gebruiker kan worden geinformeerd over het feit dat de logingegevens als gelekt zijn bestempeld en uit een te zetten wat de vervolgacties gaan zijn. |
| `nml_db_type` | Een door [PHP Data Objects (PDO)](https://www.php.net/manual/en/pdo.drivers.php) ondersteund databasetype, veelal `mysql`, `pgsql` of `sqlite3`. |
| `nml_db_file` | Alleen wanneer `nml_db_type` op `sqlite3` staat, wijst deze instelling naar de SQLite3 database file op disk. |
| `nml_db_host` | De hostname van de database server. |
| `nml_db_port` | De poort waar de database server op luistert. Veelal `3306` voor MySQL en `5432` voor PostgreSQL. |
| `nml_db_user` | De gebruikersnaam om toegang te krijgen tot de database. |
| `nml_db_pass` | Het wachtwoord dat hoort bij de gebruikersnaam om toegang te krijgen tot de database. |
| `nml_db_name` | De naam van de database waar de gegevens in zijn opgeslagen.
| `nml_db_table_prefix` | [*optioneel*] Een 'prefix' voor de gebruikte tabelnamen, mocht de database worden gedeeld met een andere applicatie. |
| `nml_datasources` | Een lijst van de te gebruiken No More Leaks datasets. De naam van de dataset komt overeen met de naam van de tabel in de database, **zonder** de `nml_db_table_prefix` ervoor wanneer deze instelling gebruikt wordt. Zie ook hieronder, kop [Database](#Database). |

### Melding aanpassen
Als er geen gebruik wordt gemaakt van de `nml_redirect_when_leaked`-configuratieoptie zal de plugin bij een gedetecteerd lek een melding tonen aan de gebruiker die zich aanmeldt. Deze melding kan worden aangepast in de `skins/{classic,elastic,larry}/templates/` directories, in het `leak_found.html`-bestand.

Voor elk in Roundcube Webmail beschikbaar *thema* (*skin*), dient een overeenkomend `leak_found.html`-bestand te bestaan. Zo kan de opmaak van de melding "vertrouwd" blijven voor de gebruiker.

## Plugin activeren
**Let op!** *Activeer de plugin binnen Roundcube Webmail alleen als bovengenoemde configuratie van de plugin is voltooid en er teminste een dataset beschikbaar is gemaakt om te doorzoeken.*

Voor de plugin door Roundcube Webmail gebruikt zal gaan worden, dient deze toegevoegd te worden in Roundcube Webmail's eigen `config/config.inc.php` configuratiebestand. Zoek hiertoe het blokje over *Plugins* op, en voeg daar `no_more_leaks` aan toe:

```
// ----------------------------------
// PLUGINS
// ----------------------------------
// List of active plugins (in plugins/ directory)
$config['plugins'] = array('no_more_leaks', 'new_user_dialog', 'emoticons');
```

## Datasets
De data in No More Leaks wordt in diverse datasets aangeboden.

Onder andere op basis van de landen waar een deelnemer actief is, of waar zijn gebruikers vandaan komen, kan de keuze gemaakt worden om één of meer datasets te importeren.

Er zijn een aantal datasets gebaseerd op geografische locaties, deze filtering gebeurt op basis van de domeinnaamextensie. Twee datasets zijn algemene datasets.

De datasets zijn elk platte tekstbestanden met op elke regel een SHA256-hash van een gelekte e-mailadres en wachtwoordcombinatie.

### Database
De enige manier om hier vlot doorheen te kunnen zoeken is door deze gegevens in een database op te slaan. In dit repository is een SQL-schema opgenomen voor tenminste de MySQL en PostgreSQL databaseserversoftware. Deze kan worden gebruikt om een systeem in te richten waar deze hashes in kunnen worden opgeslagen en doorzocht. Zie hiertoe de bestanden in de `sql/`-directory.

Gezien de gecomprimeerde datasets als platte tekst al enorm groot zijn, zullen deze datasets in een databaseserver nog enkele malen groter worden, onder andere omdat er binnen de database *lookup indexes* (kunnen) worden aangemaakt om het zoeken enorm te versnellen.

**Noot**: Het valt *sterk* aan te raden de datasets te importeren in de database *zonder* dat er reeds *lookup indexes* op de tabellen zijn aangemaakt aangezien het importeren van de datasets in de database dan aanzienlijk sneller zal verlopen!

Na het importeren kunnen de *lookup indexes* worden aangemaakt, ook dit vereist het nodige werk van de databaseserver maar omdat de *index* niet voor elke SHA256 hash gecontroleerd en bijgewerkt hoeft te worden verloopt ook dit proces vele malen sneller.

### Testdata
Er staat in de `sql/`-directory binnen de plugin ook een schema met data voor een `testdata`-set. Dit kan gebruikt worden bij het ontwikkelen van de plugin of het testen van de werking ervan.

## Class 'Check_Leak'
Het bestand `check_leak.php` in dit repository bevat de implementatie van de daadwerkelijke controle van de hash tegen de datasets in een SQL-database.

Dit is met opzet in een aparte PHP Class gezet, zodat dit makkelijker aangepast kan worden door code specifiek voor een systeem anders dan een SQL-database.

De code in `check_leak.php` **moet** een `class Check_Leak` definieren. De class **moet** een publieke functie `RunCheck` hebben. Deze functie **moet** ofwel `true` (bij een hit) ofwel `false` (bij geen hit) retourneren.

De functie `RunCheck` krijgt bij aanroep twee argumenten mee:
* Een named array met de `nml_...`-instellingen zoals in de `config.inc.php` van deze plugin zijn geconfigureerd.
* Een string met de berekende SHA256 hash voor de gebruiker die op dat moment wil inloggen.
