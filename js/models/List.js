import m from "mithril"
import Alerts from "./Alerts"
import ListConflict from "./ListConflict"

const emptyList = {
    id: 0,
    ids: "",
    name: "",
    projectId: 0,
    lastUpdate: "",
}

let content = emptyList

const serverData = {
    content: null,
}

let loading = null

function fetchListById(id) {
    return m.request({
        method: "GET",
        url: `/plugin.php`,
        params: {
            page: "TicketList/api",
            action: "list",
            id,
        },
        withCredentials: true,
    }).then(function(result) {
        if (result === null) {
            serverData.content = null
        } else {
            serverData.content = result
        }
        return result
    }).catch(function() {
        alert(`Erreur en lisant l'api /list/${id}`)
    }).finally(function() {
        loading = null;
    });
}

function parseIdsText(str) {
    const lines = str.split(/\n/)
    const ids = []
    for (let line of lines) {
        if (!line.match(/^\s*\d+/)) {
            continue;
        }
        for (let num of line.split(/[\s,]+/)) {
            if (num.length < 4 || num.length > 5) {
                continue
            }
            const id = parseInt(num, 10)
            if (id > 0) {
                ids.push(id)
            }
        }
    }
    return ids
}

function updatePageTitle() {
    const siteName = document.querySelector('#navbar-container .navbar-brand').textContent
    document.title = content.name + ' | ' + siteName
}

export default {
    get() {
        return content;
    },
    getTicketIds() {
        return parseIdsText(content.ids)
    },
    setName(name) {
        content.name = name
    },
    setText(txt) {
        content.ids = txt
    },
    setProjectId(projectId) {
        emptyList.projectId = projectId
        content.projectId = projectId
    },
    hasChanged() {
        if (!serverData.content) {
            return (content.ids.trim() !== '')
        }
        return (content.ids.trim() !== serverData.content.ids.trim());
    },
    isLoading() {
        return loading !== null;
    },
    reset() {
        serverData.content = null
        content = Object.assign({}, emptyList);
        ListConflict.reset()
    },
    delete() {
        if (content.id === 0) {
            return Promise.reject("La suppression d'une liste non-enregistrée est impossible.");
        }
        ListConflict.reset()
        return m.request({
            method: "DELETE",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "list",
            },
            body: content,
            withCredentials: true,
        }).then(function(result) {
            if (result.status === 'success') {
                serverData.content = null
                content = Object.assign({}, emptyList);
                Alerts.add(`Liste supprimée du serveur)`, 3000)
            }
            return result
        }).catch(function(message) {
            // TODO Handle the error returned by DELETE /list.
            Alerts.add(`Erreur en écrivant dans l'api DELETE /list:\n${message}`, 0)
        });
    },
    load(id) {
        ListConflict.reset()
        if ((typeof id !== 'number') || id <= 0) {
            return Promise.resolve(content)
        }
        if (loading === null) {
            loading = fetchListById(id)
        }
        loading.then(function(result) {
            content = Object.assign({}, serverData.content ?? emptyList);
            if (content.ids.match(/^[\d,]+$/)) {
                content.ids = content.ids.replaceAll(',', "\n") + "\n"
            }
            updatePageTitle()
            return content;
        })
        return loading
    },
    save() {
        return m.request({
            method: "PUT",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "list",
            },
            body: content,
            withCredentials: true,
        }).then(function(result) {
            if (result.status === 'success') {
                serverData.content = result.content
                content = Object.assign({}, result.content)
                updatePageTitle()
                Alerts.add(`Liste enregistrée sur le serveur`, 3000)
            }
            if (result.status === 'need-confirm') {
                serverData.content = result.content
                ListConflict.add(serverData.content, parseIdsText(serverData.content.ids), parseIdsText(content.ids))
                Alerts.add(`Erreur, la liste du serveur a été modifiée entre temps, ce qui crée un conflit.`, 5000)
            }
            return result
        }).catch(function(message) {
            Alerts.add(`Erreur en écrivant dans l'api PUT /list:\n${message}`, 0)
        });
    },
}
