const base64 = {
    _decode: s => Uint8Array.from(atob(s), c => c.charCodeAt(0)),
    _encode: b => btoa(String.fromCharCode(...new Uint8Array(b))),
    decodeToString: s => new TextDecoder().decode(base64._decode(s)),
    encodeString: s => base64._encode(new TextEncoder().encode(s)),
};

export {
    base64,
}
