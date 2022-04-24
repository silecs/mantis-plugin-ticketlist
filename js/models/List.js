import m from "mithril"
import Project from "./Project"

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

function fetchList(id) {
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
        console.log(projectId)
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
    load(id) {
        if (typeof id !== 'number' || id < 0) {
            return Promise.resolve(emptyList)
        }
        if (loading === null) {
            loading = fetchList(id)
        }
        loading.then(function(result) {
            content = Object.assign({}, serverData.content ?? emptyList);
            if (content.ids.match(/^[\d,]+$/)) {
                content.ids = content.ids.replaceAll(',', "\n") + "\n"
            }
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
                content = result.content
            }
            return result
        }).catch(function(message) {
            // TODO Store the error returned by PUT /list.
            alert(`Erreur en écrivant dans l'api /list:\n${message}`)
        });
    },
}
