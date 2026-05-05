"use strict";

class Rutas {

    constructor() {
        this.cargarXML();
    }

    cargarXML() {
        $.ajax({
            url: "xml/rutas.xml",
            dataType: "xml",
            method: "GET",
            success: (xml) => this.mostrarContenidoRutas(xml),
            error: (error) => console.error("Error al obtener las rutas: " + error.statusText)
        });
    }

    mostrarContenidoRutas(xml) {
        const main = document.querySelector("main");
        // Encontrar las rutas
        const rutas = xml.querySelectorAll("ruta");

        rutas.forEach((ruta) => {
            const nombre = ruta.querySelector("nombre").textContent;
            const tipo = ruta.querySelector("tipo").textContent;
            const transporte = ruta.querySelector("transporte").textContent;
            const duracion = ruta.querySelector("duracion").textContent;
            const descripcion = ruta.querySelector("descripcion").textContent.trim();
            const agencia = ruta.querySelector("agencia").textContent;
            const adecuada = ruta.querySelector("adecuadaPara").textContent.trim();
            const recomendacion = ruta.querySelector("recomendacion").textContent;
            
            const seccion = document.createElement("section");
            const h3 = document.createElement("h3");
            h3.textContent = nombre;
            seccion.appendChild(h3);

            const pTipo = document.createElement("p");
            pTipo.textContent = "Tipo: " + tipo + " | Transporte: " + transporte;
            seccion.appendChild(pTipo);

            const pDuracion = document.createElement("p");
            pDuracion.textContent = "Duración: " + duracion + " | Agencia: " + agencia;
            seccion.appendChild(pDuracion);

            const pDesc = document.createElement("p");
            pDesc.textContent = descripcion;
            seccion.appendChild(pDesc);

            const pAdecuada = document.createElement("p");
            pAdecuada.textContent = "Adecuada para: " + adecuada;
            seccion.appendChild(pAdecuada);

            const pRec = document.createElement("p");
            pRec.textContent = "Recomendación: " + recomendacion + "/10";
            seccion.appendChild(pRec);

            // Hitos
            const hitos = ruta.querySelectorAll("hito");

            hitos.forEach((hito) => {
                const nombreHito = hito.querySelector("nombre").textContent;
                const descHito = hito.querySelector("descripcion").textContent.trim();
                const distancia = hito.querySelector("distancia").textContent;
                const unidad = hito.querySelector("distancia").getAttribute("unidad");

                const artHito = document.createElement("article");

                const h4 = document.createElement("h4");
                h4.textContent = nombreHito;
                artHito.appendChild(h4);

                const pDescHito = document.createElement("p");
                pDescHito.textContent = descHito;
                artHito.appendChild(pDescHito);

                const pDist = document.createElement("p");
                pDist.textContent = "Distancia desde el anterior: " + distancia + " " + unidad;
                artHito.appendChild(pDist);

                // Fotos del hito
                const fotos = hito.querySelectorAll("foto");
                fotos.forEach((foto) => {
                    const url = foto.getAttribute("url");
                    const desc = foto.getAttribute("descripcion") || "";

                    const img = document.createElement("img");
                    img.src = url;
                    img.alt = desc;
                    artHito.appendChild(img);
                });

                seccion.appendChild(artHito);
            });

            // Altimetría SVG
            const archivoSVG = ruta.querySelector("altimetria").textContent;
            new CargadorSVG(archivoSVG, seccion);

            // Planimetría KML
            const archivoKML = ruta.querySelector("planimetria").textContent;
            new CargadorKML(archivoKML, seccion);

            main.appendChild(seccion);
        });
    }
}


class CargadorSVG {

    constructor(archivoSVG, contenedor) {
        this.cargarSVG(archivoSVG, contenedor);
    }

    cargarSVG(archivoSVG, contenedor) {
        $.ajax({
            url: "xml/" +  archivoSVG,
            dataType: "text",
            method: "GET",
            success: (textoSVG) => this.insertarSVG(textoSVG, contenedor),
            error: (error) => console.error("Error al obtener archivo: " + archivoSVG)
        });
    }


    insertarSVG(textoSVG, contenedor) {
        const parser = new DOMParser();
        const docSVG = parser.parseFromString(textoSVG, "image/svg+xml");

        const svg = docSVG.documentElement;

        const h4 = document.createElement("h4");
        h4.textContent = "Altimetría de la ruta";
        contenedor.appendChild(h4);

        contenedor.appendChild(svg);
    }
}

class CargadorKML {

    constructor(archivoKML, contenedor) {
        this.inicializarMapa(contenedor);
        this.cargarKML(archivoKML);
    }

    inicializarMapa(contenedor) {
        const h4 = document.createElement("h4");
        h4.textContent = "Planimetría de la ruta";
        contenedor.appendChild(h4);

        this.divMapa = document.createElement("div");
        this.divMapa.style.width = "100%";
        this.divMapa.style.height = "400px";
        contenedor.appendChild(this.divMapa);

        this.mapa = new google.maps.Map(this.divMapa, {
            center: { lat: 0, lng: 0 },
            zoom: 2
        });
    }

    cargarKML(archivoKML) {
        $.ajax({
            url: "xml/" +  archivoKML,
            dataType: "text",
            method: "GET",
            success: (textoKML) => this.procesarKML(textoKML),
            error: (error) => console.error("Error al obtener archivo: " + archivoKML)
        });
    }


    procesarKML(textoKML) {
        const parser = new DOMParser();
        const xml = parser.parseFromString(textoKML, "text/xml");
        
        const coordsXML = xml.getElementsByTagNameNS(
            "http://www.opengis.net/kml/2.2",
            "coordinates"
            );            
        
        this.coordenadas = [];

        for (let nodo of coordsXML) {
            const texto = nodo.textContent.trim();
            const puntos = texto.split(/\s+/);

            for (let p of puntos) {
                const [lon, lat] = p.split(",").map(Number);

                this.coordenadas.push({ lat: lat, lon: lon });
            }
        }

        this.insertarKML();
    }

    insertarKML() {
        if (!this.coordenadas || this.coordenadas.length === 0) {
            console.error("No hay coordenadas para representar.");
            return;
        }

        const origen = this.coordenadas[0];

        new google.maps.Marker({
            position: { lat: origen.lat, lng: origen.lon },
            map: this.mapa,
            title: "Inicio de la ruta"
        });

        const ruta = this.coordenadas.map(p => ({ lat: p.lat, lng: p.lon }));

        const polilinea = new google.maps.Polyline({
            path: ruta,
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 3
        });

        polilinea.setMap(this.mapa);

        const bounds = new google.maps.LatLngBounds();

        for (let punto of ruta) {
            bounds.extend(punto);
        }

        this.mapa.fitBounds(bounds);
    }

}
