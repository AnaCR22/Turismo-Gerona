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
    NS = {'u': 'http://www.uniovi.es'}

    # Cargar XML
    try:
        raiz = ET.parse(xml_path).getroot()
    except (IOError, ET.ParseError):
        print("Error procesando el archivo XML")
        return
    

    rutas = raiz.findall('u:ruta', NS)
    
    # Iterar sobre cada ruta
    for ruta in rutas:
        nombre_ruta = ruta.findtext('u:nombre', default='Ruta', namespaces=NS)
        id_ruta = ruta.get('id')

        # Generar KML para cada ruta
        nuevoKML = Kml()
        coords = []

        # Punto de inicio de la ruta
        inicio = ruta.find('u:inicio/u:coordenadas', NS)
        lon = inicio.findtext('u:longitud', namespaces=NS)
        lat = inicio.findtext('u:latitud', namespaces=NS)
        alt = inicio.findtext('u:altitud', namespaces=NS)
        coords.append((lon, lat, alt))
        nuevoKML.addPlacemark(nombre_ruta + " - Inicio", "Punto de inicio de la ruta", lon, lat, alt, 'relativeToGround')
        
        # Recorrer los hitos de las rutas
        hitos = ruta.find('u:hitos', NS)

        # Recorrer los hitos de las rutas
        for hito in hitos:
            # Quitar el tag para comparar
            tag = hito.tag.replace('{http://www.uniovi.es}', '')

            coord = hito.find('u:coordenadas', NS)
            lon = coord.findtext('u:longitud', namespaces=NS)
            lat = coord.findtext('u:latitud', namespaces=NS)
            alt = coord.findtext('u:altitud', namespaces=NS)
            coords.append((lon, lat, alt))

            if tag == 'hito':
                nombre_hito = hito.findtext('u:nombre', namespaces=NS)
                desc_hito = hito.findtext('u:descripcion', namespaces=NS)
                nuevoKML.addPlacemark(nombre_hito, desc_hito, lon, lat, alt, 'relativeToGround')

        # Línea con todas las coordenadas
        coordenadas_str = "\n".join(f"{lon},{lat},{alt}" for lon, lat, alt in coords)

        nuevoKML.addLineString(
            nombre_ruta, "1", "1",
            coordenadas_str,
            'relativeToGround',
            '#ff0000ff', "5"
        )

        nombre_archivo = ruta.findtext('u:planimetria', namespaces=NS)
        nuevoKML.escribir(nombre_archivo)

        print("Creado el archivo:", nombre_archivo)


if __name__ == "__main__":
    main()
