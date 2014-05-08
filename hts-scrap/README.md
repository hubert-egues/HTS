Ejecutar comando:

	ingresar a la carpeta "hts-scrap"
	ejecutar: 
		php application.php hts-crawler
	en la misma carpeta se encontrara el archivo "hts.json" con los hts descargados de http://hts.usitc.gov/by_chapter.html: 
	
	Luego se continuaria con el proceso que no se altero, que es el de asignar nombres
	ingrear a la carpeta: "Hts_final"
	mover el  archivo:
		"hts-scrap/hst.json"
	hacia:
		"Hts_final/Hts_Usa/input/hts_usa.json"
	ejecutar:
		python NameHts.py
	esto generar un archivo final en:
		"Hts_final/Hts_Usa/output/output.json"
