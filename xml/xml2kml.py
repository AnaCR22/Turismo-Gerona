# 02020-KML-Rutas.py
# -*- coding: utf-8 -*-
"""
Generación de archivo KML usando expresiones xPath

@author: Ana Calleja Ramón
Universidad de Oviedo
"""

import xml.etree.ElementTree as ET

class Kml(object):

    def __init__(self):
        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz, 'Document')

    def addPlacemark(self, nombre, descripcion, lon, lat, alt, modoAltitud):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = nombre
        ET.SubElement(pm, 'description').text = descripcion
        punto = ET.SubElement(pm, 'Point')
        ET.SubElement(punto, 'coordinates').text = f"{lon},{lat},{alt}"
        ET.SubElement(punto, 'altitudeMode').text = modoAltitud

    def addLineString(self, nombre, extrude, tesela,
                      listaCoordenadas, modoAltitud, color, ancho):

        ET.SubElement(self.doc, 'name').text = nombre
        pm = ET.SubElement(self.doc, 'Placemark')
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls, 'extrude').text = extrude
        ET.SubElement(ls, 'tessellation').text = tesela
        ET.SubElement(ls, 'coordinates').text = listaCoordenadas
        ET.SubElement(ls, 'altitudeMode').text = modoAltitud

        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement(linea, 'color').text = color
        ET.SubElement(linea, 'width').text = ancho

    def escribir(self, nombreArchivoKML):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoKML, encoding='utf-8', xml_declaration=True)

    def ver(self):
        print("\nElemento raíz =", self.raiz.tag)
        for hijo in self.raiz.findall('.//'): 
            print("\nElemento =", hijo.tag)
            print("Contenido =", hijo.text)
            print("Atributos =", hijo.attrib)


def main():
    xml_path = "rutasEsquema.xml"

    # Namespace del XML
    NS = {'ns': 'http://www.uniovi.es'}

    # Cargar XML
    try:
        raiz = ET.parse(xml_path).getroot()
    except (IOError, ET.ParseError):
        print("Error procesando el archivo XML")
        return
    

    rutas = raiz.findall('ns:ruta', NS)
    
    # Iterar sobre cada ruta
    for ruta in rutas:
        nombre_ruta = ruta.findtext('ns:nombre', default='Ruta', namespaces=NS)

        #Generar KML para cada ruta
        nuevoKML = Kml()
        coords = []

        #Punto de inicio de la ruta
        lon = ruta.findtext('ns:inicio/ns:coordenadas/ns:longitud', namespaces=NS)
        lat = ruta.findtext('ns:inicio/ns:coordenadas/ns:latitud', namespaces=NS)
        alt = ruta.findtext('ns:inicio/ns:coordenadas/ns:altitud', namespaces=NS)
        coords.append((lon, lat, alt))
        nuevoKML.addPlacemark(nombre_ruta + " - Inicio", "Punto de inicio de la ruta", lon, lat, alt, 'relativeToGround')
        
        #Recorrer los hitos de las rutas
        hitos = ruta.find('ns:hitos', NS)

        for hito in hitos:
            lon = hito.findtext('ns:coordenadas/ns:longitud', namespaces=NS)
            lat = hito.findtext('ns:coordenadas/ns:latitud', namespaces=NS)
            alt = hito.findtext('ns:coordenadas/ns:altitud', namespaces=NS)
            coords.append((lon, lat, alt))

            nombre_hito = hito.findtext('ns:nombre', namespaces=NS)
            if nombre_hito:
                desc_hito = hito.findtext('ns:descripcion', namespaces=NS)
                nuevoKML.addPlacemark(nombre_hito, desc_hito, lon, lat, alt, 'relativeToGround')

        #Línea con todas las coordenadas para mostrar
        coordenadas_str = "\n".join(f"{lon},{lat},{alt}" for lon, lat, alt in coords)

        nuevoKML.addLineString(
            nombre_ruta, "1", "1",
            coordenadas_str,
            'relativeToGround',
            '#ff0000ff', "5"
        )

        nuevoKML.ver()
        nombre_archivo = ruta.findtext('ns:planimetria', namespaces=NS)
        nuevoKML.escribir(nombre_archivo)

        print("Creado el archivo:", nombre_archivo)


if __name__ == "__main__":
    main()
