import m from "mithril"
import Project from "./models/Project"
import Main from "./views/Main"
import MainByName from "./views/MainByName"

document.addEventListener('DOMContentLoaded', function() {
    // Default project is defined in the static HTML
    Project.readJsonBlock('ticket-list-data')
    const projectId = Project.get().id

    const rootElement = document.getElementById('ticketlist-container')
    if (rootElement === null) {
        return;
    }
    m.route(
        rootElement,
        `/project/${projectId}/list/new`, // default route
        {
            // Name ':key' implies new component on value change
            // Optional URL param 'issueIds'
            "/project/:projectId/list/:key": Main,

            // Identify a list by its name instead of its ID,
            // then redirect, forwarding the URL param 'issueIds'.
            "/project/:projectId/name/:listName": MainByName,
        }
    );
}, false);
