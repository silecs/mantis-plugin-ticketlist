import m from "mithril"
import Project from "./models/Project"
import Main from "./views/Main"
import MainByIds from "./views/MainByIds"

document.addEventListener('DOMContentLoaded', function() {
    // Default project is defined in the static HTML
    Project.readJsonBlock('ticket-list-data')
    const projectId = Project.get().id

    const rootElement = document.getElementById('ticketlist-container')
    if (rootElement === null) {
        alert("invalid HTML")
        return;
    }
    m.route(
        rootElement,
        `/project/${projectId}/list/new`, // default route
        {
            "/project/:projectId/list/:key": Main, // Name ':key' implies new component on value change
            "/project/:projectId/new/:issueIds": MainByIds,
            // TODO Add a route for operations on issues (closing and setting a release)
        }
    );
}, false);
