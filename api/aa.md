# EAP: Architecture Specification and Prototype

> EventSquare is a fast, simple, and accessible web platform that helps people discover events and take part in them. It centralizes event creation, discovery, and participation while supporting private and public visibility. Administrators supervise the platform.

## A7: Web Resources Specification
<!--
> Brief presentation of the artifact's goals.
-->
This document describes how the EventSquare web app is built.<br>
It lists the different types of data the app uses, what information each one contains, and what the JSON responses look like.<br>
It also explains the basic actions we can do with each type of data: create, read, update, and delete (CRUD).<br>
The description follows the OpenAPI standard and is written in YAML format.


### 1. Overview
<!--
> Identify and overview the modules that will be part of the application.  
-->
This section presents a view of the web application, listing its main modules and summarising their purpose. The specific web resources included in the vertical prototype are described in detail for each module in the corresponding OpenAPI specification.

<table>
  <tr>
    <td><strong>M01: Authentication & Profile</strong></td>
    <td>Web resources associated with user authentication and profile management. Includes the following system features: user registration, login and logout, session management, viewing the current user’s profile, and (in the full system) editing basic profile information such as name, username and profile picture.</td>
  </tr>
  <tr>
    <td><strong>M02: Events</strong></td>
    <td>Web resources associated with events. Includes the following system features: browsing and searching public events, viewing event details (date, time, venue, capacity), and, for organizers, creating new events, editing existing event information, and cancelling/deleting events.</td>
  </tr>
  <tr>
    <td><strong>M03: Participation (Applications & Invitations)</strong></td>
    <td>Web resources associated with user participation in events. Includes the following system features: submitting applications to join events, viewing the status of my applications and participations, sending invitations to other users, and accepting or declining received invitations.</td>
  </tr>
  <tr>
    <td><strong>M04: Interactions (Comments & Polls)</strong></td>
    <td>Web resources associated with user interaction around events. Includes the following system features: adding and listing comments on events, deleting one’s own comments, creating and viewing polls attached to events, and voting in polls.</td>
  </tr>
  <tr>
    <td><strong>M05: Admin & Static</strong></td>
    <td>Web resources associated with administrative features and static informational pages. Includes the following system features: administrator dashboards and management tools (e.g., moderating content or users in the full system), as well as static pages such as home, about, contact, help/FAQ and other informational content.</td>
  </tr>
</table>

<br><br>

### 2. Permissions

> This section describes the permission codes used in the modules to define who is allowed to access each web resource in EventSquare.

| **Code** | **Role** | **Description** |
| --- | --- | --- |
| **PUB** | Public | Guest visitors without an authenticated session. They can access publicly available pages and public events. |
| **USR** | User | Authenticated users with a regular EventSquare account. They can manage their own participation, interact with events, and use standard user features. |
| **OWN** | Owner | The user who owns a specific resource (for example, the author of a comment or the owner of a profile). Only owners may perform certain actions on their own resources. |
| **ORG** | Organizer | The organizer of an event. Organizers are the resource owners for events and have additional permissions such as creating, editing, and cancelling their events. |
| **ADM** | Administrator | EventSquare administrators. They have elevated permissions for moderation and system management tasks. |
  
<br><br>

### 3. OpenAPI Specification
<!--
> OpenAPI specification in YAML format to describe the vertical prototype's web resources.

> Link to the `a7_openapi.yaml` file in the group's repository.
-->
This section contains the complete EventSquare web API specification in OpenAPI (YAML) for the vertical prototype(A8) implemented.<br>
A copy of the OpenAPI YAML file is also provided in the project’s GitLab repository.


```yaml
openapi: 3.0.0

...
```

---

<br><br>
<br><br>

## A8: Vertical prototype
<!--
> Brief presentation of the artifact goals.
-->
This prototype is a small, working slice of the product. It includes the highest-priority features (the ones marked with *). <br>
We build it with the LBAW Framework and touch every layer: the user interface, the business rules, and the database. It has pages to view, add, edit, and delete information, controls who can access what, and shows clear success and error messages.

### 1. Implemented Features

#### 1.1. Implemented User Stories

The implemented user stories for the prototype are summarized in the table that follows.
<!--
> Identify the user stories that were implemented in the prototype.  
-->
| User Story reference | Name      | Priority    | Responsible        | Description                                           |
| -------------------- | --------- | ----------- | ------------------ | ----------------------------------------------------- |
| US01                 | Browse Public Events | Prototype |Filipe Camacho| As a User, I want to browse public events without signing in, so that I can decide whether to register.
| US02                 | View Public Event Details | Prototype |Filipe Camacho| As a User, I want to view a public event’s page, so that I can decide whether it’s worth creating an account to join.
| US04                 | Search Events | Prototype |Filipe Camacho| As a User, I want to search events(exact/full-text), so that I can quickly find matches.
| VT01                 | Sign Up | Prototype | Beneeta Mendez | As a Visitor, I want to register, so that I can participate in events.
| VT02                 | Sign In | Prototype | Beneeta Mendez | As a Visitor, I want to sign in, so that I can access my account.
| RU01                 | View Profile | Prototype |Isabel Sousa| As a Registered user, I want to view my profile, so that I can verify my personal details are up to date.
| RU02                 | Edit Profile | Prototype |Isabel Sousa| As a Registered user, I want to edit my profile, so that my personal details stays up to date.
| RU08                | Logout | Prototype | Beneeta Mendez | As a Registered user, I want to log out, so that I can securely end my session on a shared device.
| RU09                | Apply to Event | Prototype |Isabel Sousa| As a Registered user, I want to request to join an event, so that I can participate.
| RU10                | Accept/Reject Invitation | Prototype | Filipe Camacho| As a Registered user, I want to accept or decline an event invitation, so that the organizer knows whether I will participate.
| OR01               | Create Event | Prototype |Beneeta Mendez  | As an Organizer, I want to create an event with date, time, location, so that people can find it and join.
| OR02               | Edit Event | Prototype | Beneeta Mendez | As an Organizer, I want to edit my event details, so that the event information stays up to date.
| OR03               | Invite User | Prototype |Filipe Camacho| As an Organizer, I want to invite users, so that the right people are notified immediately.
| OR08               | Delete Event | Prototype | Beneeta Mendez | As an Organizer, I want to delete an event so that any mistakenly published information is removed from the system.
| AD07              | Administer User Account | Prototype |Filipe Camacho| As an Administrator, I want to search, view, create, and edit users, so that the platform stays well-maintained.

<br><br>

#### 1.2. Implemented Web Resources
<!--
> Identify the web resources that were implemented in the prototype.  
-->
The web resources that were implemented in the prototype are described below.

<br>

##### Module M01: Authentication and Profile

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R101: Login Form | GET /login |
| R102: Login Action | POST /login |
| R103: Logout Action | POST /logout |
| R104: Register Form | GET /register |
| R105: Register Action | POST /register |


<br>

##### Module M02: Events 

| Web Resource Reference | URL |
| ---------------------- | --- |
| R201: Browse events list | GET /events |
| R202: Create event form | GET /events/create |
| R203: Event details (view) | GET /events/{eventId} |
| R204: Edit event form | GET /events/{eventId}/edit |
| R205: Update event (action) | PUT /events/{eventId} |
| R206: Delete event (action) | DELETE /events/{eventId} |
| R207: Create event (action) | POST /events |
| R208: My events (organizer list) | GET /my-events |

<br>

##### Module M03: Participation (Invitations)

| Web Resource Reference | URL |
| ---------------------- | --- |
| R301: Invite user to event | POST /events/{eventId}/invite |
| R302: Accept invitation | POST /invitations/{invitationId}/accept |
| R303: Decline invitation | POST /invitations/{invitationId}/decline |
| R304: List my invitations | GET /my-invitations |

<br>

##### Module M05: Admin & Static (User Administration)

| Web Resource Reference | URL |
| ---------------------- | --- |
| AD01: List/Search users | GET /admin/users |
| AD02: Create user form | GET /admin/users/create |
| AD03: Create user (action) | POST /admin/users |
| AD04: Show user | GET /admin/users/{userId} |
| AD05: Edit user form | GET /admin/users/{userId}/edit |
| AD06: Update user (action) | PUT /admin/users/{userId} |

<br>



<br><br>
### 2. Prototype

> Command to start the Docker image from the group's Container Registry.
> User credentials necessary to test all features.
> Link to the source code in the group's Git repository.
>
Admin Login

Email: admin.*@eventsquare.pt
Password: adminhash123

User Login

Email: *@email.com
Password: EventSquare123!





## Revision history

Changes made to the first submission:
1. Added Invitations feature (invite, accept, decline, list) – M03 resources R301–R304.
2. Added Admin user management (list, create, show, edit, update) – AD01–AD06.
3. Expanded Events module resource list (browse, edit form, update action, my events) – R201, R204, R205, R208.
4. Standardized verbs (PUT for update, DELETE for deletion) to match REST and current implementation.
5. Added organizer-specific and invitee-specific participation endpoints.

***
GROUP2536, 23/11/2025
 
* Filipe Mexedo Guerra Ferraz Camacho, up202208040@fe.up.pt (Editor)
* Isabel Machado e Sousa, up201906379@fe.up.pt 
* Beneeta Mendez, up201510376@fe.up.pt