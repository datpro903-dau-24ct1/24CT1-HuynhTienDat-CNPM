export const API = "https://itconect.free.je/api";

export const getMediaUrl = (path) => (!path ? null : /^https?:\/\//.test(path) ? path : `${API}/${path}`);

const ICONS = { friend_request: "👥", friend_accept: "🤝", message: "💬" };
export const getNotificationIcon = (type) => ICONS[type] || "🔔";