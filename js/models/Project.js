import m from "mithril"
import Alerts from "./Alerts"

const MANAGER_LEVEL = 70

let content = {
    id: 0,
    name: "Tous les projets",
    accessLevel: 10,
}

let loading = null

export default {
    get() {
        return content;
    },
    hasManagerRights() {
        return (content.accessLevel ?? 10) >= MANAGER_LEVEL;
    },
    isLoading() {
        return loading !== null;
    },
    load(id) {
        if (loading !== null) {
            return loading;
        }
        if (content.id === id) {
            // Do not query if the project has not changed.
            return Promise.resolve(content)
        }
        if (id === 0) {
            content.id = 0
            content.name = "Tous les projets"
            return Promise.resolve(content)
        }

        content.id = id
        content.name = "Chargement…"

        loading = m.request({
            method: "GET",
            url: "/plugin.php",
            params: {
                page: "TicketList/api",
                action: "project",
                id,
            },
            withCredentials: true,
        }).then(function(result) {
            content = result;
        }).catch(function() {
            Alerts.add(`Erreur en lisant l'api /project/${id}`, 0)
        }).finally(function() {
            loading = null;
        });
        return loading
    },
    readJsonBlock(name) {
        const htmlElement = document.getElementById(name)
        if (htmlElement === null) {
            console.log(`invalid HTML : element #${name} was not found.`)
            return;
        }
        content = JSON.parse(htmlElement.textContent)
    }
}
