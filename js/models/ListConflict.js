
let conflict = {}

export default {
    add(serverList, serverIds, localIds) {
        conflict.serverList = serverList
        conflict.serverIds = serverIds
        conflict.localIds = localIds
    },
    get() {
        return conflict
    },
    isEmpty() {
        return !conflict.hasOwnProperty('serverList')
    },
    reset() {
        conflict = {}
    },
}
