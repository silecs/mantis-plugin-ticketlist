import m from "mithril"

const emptyList = {
    id: 0,
    ids: "",
    name: "",
    projectId: 0,
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
        content = Object.assign({}, serverData.content ?? emptyList);
        if (content.ids.match(/^[\d,]+$/)) {
            content.ids = content.ids.replace(',', "\n") + "\n"
        }
        return content;
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
    hasChanged() {
        return !serverData.content || (serverData.content.ids !== content.ids)
    },
    isLoading() {
        return loading !== null;
    },
    load(id) {
        if (loading !== null) {
            return loading;
        }
        loading = fetchList(id)
        return loading
    },
}
