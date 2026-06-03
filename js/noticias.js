class Noticias {

    busqueda;
    url = "https://api.thenewsapi.com/v1/news/all";
    apiKey = "2lNWy32fAtPwfhyUilUPAwrcahpmMgY8ejdOPI0e"

    constructor(busqueda) {
        this.busqueda = busqueda;
        this.noticias = [];
    }

    buscar() {
        const urlCompleta =
            `${this.url}?api_token=${this.apiKey}` +
            `&search=${encodeURIComponent(this.busqueda)}` +
            `&language=es` +
            `&limit=5`;
            
        $.ajax({
            url: urlCompleta,
            dataType: "json",
            method: "GET",
            success: (json) => this.procesarInformacion(json),
            error: (error) => console.error("Error al obtener las noticias: " + error.statusText)
        });
    }

    procesarInformacion(json) {
        if (!json || !json.data) return;

        this.noticias = json.data;

        this.mostrarNoticias();
    }

    mostrarNoticias() {
        const main = document.querySelector("main");

        //Crear sección para las noticias
        const seccion = document.createElement("section");

        const h2 = document.createElement("h2");
        h2.textContent = "Noticias relacionadas con Gerona"

        seccion.appendChild(h2);

        // Crear un artículo por cada noticia
        this.noticias.forEach(noticia => {
            const article = document.createElement("article");

            const h3 = document.createElement("h3");
            h3.textContent = noticia.title;
            article.appendChild(h3);

            const p = document.createElement("p");
            p.textContent = noticia.description || "Sin descripción";
            article.appendChild(p);

            if (noticia.source && noticia.url) {
                const fuente = document.createElement("p");
                fuente.textContent = "Fuente: ";

                const enlace = document.createElement("a");
                enlace.href = noticia.url;
                enlace.target = "_blank"; //abrir enlace en pestaña nueva
                enlace.textContent = noticia.source;

                fuente.appendChild(enlace);
                article.appendChild(fuente);
            }

            seccion.appendChild(article);
        });

        main.appendChild(seccion);
    }
}
