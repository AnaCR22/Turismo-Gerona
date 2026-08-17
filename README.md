# Gerona-Desktop

Gerona-Desktop is a web application focused on providing tourist information about Girona while allowing users to manage reservations for tourist resources.

The project combines dynamic web development, relational data management, structured data processing and cloud deployment.

## Features

### Tourist information

* Information about Girona and its gastronomy.
* Tourist routes with detailed information about duration, type and recommended use.
* Route milestones with coordinates, descriptions and multimedia content.
* Weather information.
* Interactive map visualisation.

### Route visualisation

Route information is stored in XML and processed dynamically in the browser.

The application incorporates:

* **XML** for structured route data.
* **DTD and XSD** for data validation.
* **SVG** for route elevation profiles.
* **KML** for route mapping and geographic information.

### Reservations

* User registration and authentication.
* Browse available tourist resources.
* Generate reservation budgets.
* Confirm and cancel reservations.
* View previously created reservations.

### Interactive content

* JavaScript-based interactive components.
* Tourist game.
* Multimedia content integrated into the website.

## Architecture

The server-side functionality is implemented in **PHP using object-oriented programming**, with responsibilities separated into dedicated classes:

```text
php/
├── conexion.php
├── usuario.php
├── recursos.php
├── presupuesto.php
├── reservas.php
└── misreservas.php
```

The application uses PHP as the server-side layer and MySQL as the relational database.

The frontend combines HTML, CSS and JavaScript, with jQuery used for asynchronous content loading and interaction.

## Database

The application uses a normalised **MySQL relational database** consisting of five related tables:

```text
USUARIOS
    │
    ├── RESERVAS ─── RECURSOS ─── TIPOS_RECURSO
    │
    └── PRESUPUESTOS
```

The database stores user accounts, tourist resources, reservations and budgets, with primary and foreign key relationships between entities.

## Data & Web Standards

The project makes use of several web and data standards:

* **HTML5**
* **CSS3**
* **JavaScript**
* **jQuery**
* **PHP**
* **MySQL**
* **XML**
* **DTD**
* **XSD**
* **SVG**
* **KML**

The route data is validated using XML schema definitions, while route elevation and geographic information are represented through SVG and KML respectively.

## Accessibility

Accessibility was considered throughout the development process.

The static and dynamic HTML was validated, CSS was checked for standards compliance, and the resulting pages were evaluated using **WCAG 2.0 AAA** accessibility checks.

## Usability Testing

The application was evaluated through **three iterative usability testing rounds**, with four participants per round.

The tested tasks included:

* Finding information about a tourist route.
* Checking the current weather in Girona.
* Completing a reservation from registration to confirmation.

Findings from each round were used to improve the interface and user flow, including:

* Improving mobile and tablet controls.
* Making the reservation process clearer.
* Adding confirmation messages.
* Improving the budget breakdown.
* Refining responsive layouts.

## Deployment

The application was deployed to a **Microsoft Azure Virtual Machine**.

The deployment environment includes:

```text
Microsoft Azure VM
        │
        ├── Apache
        ├── PHP
        └── MySQL
```

The server was configured and accessed through SSH, with the application and database deployed directly to the virtual machine.

## Project Structure

```text
sew/
├── php/           # Server-side PHP and database operations
├── js/            # Client-side JavaScript
├── xml/           # Route data, schemas and geographic resources
├── estilo/        # CSS
├── multimedia/    # Images, video and audio
└── *.html         # Main application pages
```

## Getting Started

### Requirements

* Apache
* PHP
* MySQL
* A web browser

### Installation

1. Clone the repository.
2. Configure the Apache web server.
3. Create the required MySQL database using the provided SQL script.
4. Configure the database connection.
5. Place the project in the web server directory.
6. Open the application through the local server.

> **Security:** database credentials and API keys must be configured locally and should never be committed to the repository.

## Project Context

Academic project developed as part of the **Software and Web Standards** course at the University of Oviedo.

**Author:** Ana Calleja Ramón
