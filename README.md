# wp-rough-collie
WordPress theme for Rough Collie, child of twentyfifteen


## Data needed for a dog

result = mysql_query("SELECT 
IdentificatieCombinatie, 
IdentificatieKleur, 
IdentificatieRas, 
IdentificatieFokker, 
Geslacht, 
Geboortedatum, 
Overlijdingsdatum, 
Opmerkingen, 
Bijzonderheden, 	
Volgnr, 
IdentificatieTitelVoorNaam, 
Naam, 
RegistratienummerMoeder, 
RegistratienummerVader, 	
IdentificatieTitelAchterNaam, 
TentoonstellingJN, 
Statuskleur, 
AVK, 
Roepnaam FROM ".$prefix."dier WHERE 
Registratienummer ='$link' AND 
Diersoort > '0' LIMIT 1", $dbi) 

Displayed data
Pedigree number (stamboomnummer)
Titel / Title
Kleur / Colour
Geslacht / Sex
Geboortedatum / Date of birth
Overlijdensdatum / Date of decease
Kennel of Fokker / Breeder

Link to:
- Stamboom / Pedigree
- Nakomelingen / Offspring
- Tentoonstellingen / Shows


## Data needed for Breeder