class Noticias {

    busqueda;
    url = "https://api.thenewsapi.com/v1/news/all";
    apiKey = "2lNWy32fAtPwfhyUilUPAwrcahpmMgY8ejdOPI0e"

    constructor(busqueda) {
        this.busqueda = busqueda;
        this.noticias = [];
    }

    async buscar() {
        const urlCompleta =
            `${this.url}?api_token=${this.apiKey}` +
            `&search=${encodeURIComponent(this.busqueda)}` +
            `&language=es` +
            `&limit=5`;
            
        try{
            const respuesta = await fetch(urlCompleta);
            if (!respuesta.ok) {
                throw new Error("Error al obtener las noticias");
            }            
            const json = await respuesta.json();
            this.procesarInformacion(json);
        } catch (error){
            console.error("Error al obtener las noticias: " +  error.message);
        }
    }

    procesarInformacion(json) {
        if (!json || !json.data) return;

        this.noticias = json.data;

        this.mostrarNoticias();
    }

    mostrarNoticias() {
        const main = document.querySelector("main");

        const seccion = document.createElement("section");

        const h2 = document.createElement("h2");
        h2.textContent = "Noticias relacionadas con Gerona"

        seccion.appendChild(h2);

        // Crear un artículo por cada noticia
        this.noticias.forEach(noticia => {

            const h3 = document.createElement("h3");
            h3.textContent = noticia.title;
            seccion.appendChild(h3);

            const p = document.createElement("p");
            p.textContent = noticia.description || "Sin descripción";
            seccion.appendChild(p);

            if (noticia.source && noticia.url) {
                const fuente = document.createElement("p");
                fuente.textContent = "Fuente: ";

                const enlace = document.createElement("a");
                enlace.href = noticia.url;
                enlace.target = "_blank";
                enlace.textContent = noticia.source;

                fuente.appendChild(enlace);
                seccion.appendChild(fuente);
            }

        });

        main.appendChild(seccion);
    }
}
