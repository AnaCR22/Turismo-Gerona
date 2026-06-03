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
            const inicio = ruta.querySelector("inicio");

            const seccion = document.createElement("section");
            const h3 = document.createElement("h3");
            h3.textContent = nombre;
            seccion.appendChild(h3);

            const pDesc = document.createElement("p");
            pDesc.textContent = descripcion;
            seccion.appendChild(pDesc);

            const ul = document.createElement("ul");
            const datos = [
                "Tipo: " + tipo,
                "Transporte: " + transporte,
                "Duración: " + duracion,
                "Agencia: " + agencia,
                "Adecuada para: " + adecuada,
                "Recomendación: " + recomendacion + "/10"
            ];

            datos.forEach((texto) => {
                const li = document.createElement("li");
                li.textContent = texto;
                ul.appendChild(li);
            });

            seccion.appendChild(ul);

            //Punto origen
            const lugar = inicio.querySelector("lugar").textContent;
            const direccion = inicio.querySelector("direccion").textContent;

            const h4Inicio = document.createElement("h4");
            h4Inicio.textContent = "Punto de origen: " + lugar;
            seccion.appendChild(h4Inicio);

            const pDir = document.createElement("p");
            pDir.textContent = "Dirección: " + direccion;
            seccion.appendChild(pDir);

            // Hitos
            const hitos = ruta.querySelectorAll("hito");
            let distanciaAcumulada = 0;
            let contadorHito = 1;

            hitos.forEach((hito) => {
                //Calcular distancia entre hitos importantes
                const distancia = parseFloat(hito.querySelector("distancia").textContent);
                distanciaAcumulada += distancia;

                const elementoNombre = hito.querySelector("nombre");
                if(elementoNombre){
                    const nombreHito = elementoNombre.textContent;
                    const descHito = hito.querySelector("descripcion").textContent.trim();
                    const distUnidad = hito.querySelector("distancia").getAttribute("unidad");
                    
                    //Creamos un article por cada hito
                    const artHito  = document.createElement("article");

                    const h4 = document.createElement("h4");
                    h4.textContent = "Hito " + contadorHito + ": " + nombreHito;
                    artHito.appendChild(h4);

                    const pDescHito = document.createElement("p");
                    pDescHito.textContent = descHito;
                    artHito.appendChild(pDescHito);

                    const lon = hito.querySelector("coordenadas longitud").textContent;
                    const lonUnidad = hito.querySelector("coordenadas longitud").getAttribute("unidad");
                    const lat = hito.querySelector("coordenadas latitud").textContent;
                    const latUnidad = hito.querySelector("coordenadas latitud").getAttribute("unidad");
                    const alt = hito.querySelector("coordenadas altitud").textContent;
                    const altUnidad = hito.querySelector("coordenadas altitud").getAttribute("unidad");

                    const pCoords = document.createElement("p");
                    pCoords.textContent = "Latitud: " + lat + " " + latUnidad + " | Longitud: " + lon + " " + lonUnidad + " | Altitud: " + alt + " " + altUnidad;
                    artHito.appendChild(pCoords);

                    const pDist = document.createElement("p");
                    pDist.textContent = "Distancia desde el hito anterior: " + distanciaAcumulada + " " + distUnidad;
                    artHito.appendChild(pDist);

                    // Multimedia del hito
                    const h5 = document.createElement("h5");
                    h5.textContent = "Multimedia del hito " +  contadorHito;
                    artHito.appendChild(h5);
                    
                    const fotos = hito.querySelectorAll("foto");
                    fotos.forEach((foto) => {
                        const url = foto.getAttribute("url");
                        const desc = foto.getAttribute("descripcion") || "";

                        const img = document.createElement("img");
                        img.src = url;
                        img.alt = desc;
                        artHito.appendChild(img);
                    });

                    const videos = hito.querySelectorAll("video");
                    videos.forEach((video) => {
                        const url = video.getAttribute("url");
                        const desc = video.getAttribute("descripcion") || "";

                        const elementVideo = document.createElement("video");
                        elementVideo.src = url;
                        elementVideo.controls = true;
                        elementVideo.textContent = desc;
                        artHito.appendChild(elementVideo);
                    });

                    seccion.appendChild(artHito);
                    distanciaAcumulada = 0;
                    contadorHito++;
                }
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
        this.contenedor = contenedor;
        this.cargarKML(archivoKML);
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
        const ns = "http://www.opengis.net/kml/2.2";

        // Coordenadas de la LineString (para la polilínea)
        this.coordenadas = [];
        const lineStrings = xml.getElementsByTagNameNS(ns, "LineString");
        for (let ls of lineStrings) {
            const texto = ls.getElementsByTagNameNS(ns, "coordinates")[0].textContent.trim();
            const puntos = texto.split(/\s+/);
            for (let p of puntos) {
                const [lon, lat] = p.split(",").map(Number);
                this.coordenadas.push([lon, lat]);
            }
        }

        // Placemarks con Point (para los marcadores)
        this.marcadores = [];
        const placemarks = xml.getElementsByTagNameNS(ns, "Placemark");
        for (let pm of placemarks) {
            const point = pm.getElementsByTagNameNS(ns, "Point")[0];
            if (point) {
                const nombre = pm.getElementsByTagNameNS(ns, "name")[0]?.textContent || "";
                const coords = point.getElementsByTagNameNS(ns, "coordinates")[0].textContent.trim();
                const [lon, lat] = coords.split(",").map(Number);
                this.marcadores.push({ lon: lon, lat: lat, nombre: nombre });
            }
        }

        this.insertarKML();
    }

    insertarKML() {
        if (!this.coordenadas || this.coordenadas.length === 0) {
            console.error("No hay coordenadas para representar.");
            return;
        }

        const h4 = document.createElement("h4");
        h4.textContent = "Planimetría de la ruta";
        this.contenedor.appendChild(h4);

        //crear div para el mapa
        let divMapa = document.createElement("div");
        this.contenedor.appendChild(divMapa);

        // 2. Inicializar Mapbox
        mapboxgl.accessToken = "pk.eyJ1IjoiYW5uY3J4IiwiYSI6ImNtcHd3ZHo5NTAwNWoyc3BlZXI3a3JtdjEifQ.azFHN1htuUXF-vK3vmZF6w";
        const mapa = new mapboxgl.Map({
            container: divMapa,
            center: [this.coordenadas[0][0], this.coordenadas[0][1]],
            zoom: 15
        });

        mapa.on('load', () => {
            // Polilínea
            mapa.addSource('ruta', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: this.coordenadas
                    }
                }
            });

            mapa.addLayer({
                id: 'ruta-linea',
                type: 'line',
                source: 'ruta',
                paint: {
                    'line-color': '#FF0000',
                    'line-width': 5
                }
            });

            // Marcadores
            for (let m of this.marcadores) {
                new mapboxgl.Marker()
                    .setLngLat([m.lon, m.lat])
                    .setPopup(new mapboxgl.Popup().setText(m.nombre))
                    .addTo(mapa);
            }

            const bounds = new mapboxgl.LngLatBounds();
            for (let coord of this.coordenadas) {
                bounds.extend(coord);
            }
            mapa.fitBounds(bounds, { padding: 50 });
        });
    }
}
