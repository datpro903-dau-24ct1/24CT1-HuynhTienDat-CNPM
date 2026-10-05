export const API = "https://itconect.free.je/api";

export const getMediaUrl = (path) => (!path ? null : /^https?:\/\//.test(path) ? path : `${API}/${path}`);

const ICONS = { friend_request: "👥", friend_accept: "🤝", message: "💬" };
export const getNotificationIcon = (type) => ICONS[type] || "🔔";

// Dung lượng tối đa của tệp đính kèm khi đăng bài (MB) - InfinityFree chỉ cho phép tối đa 10MB/file
export const MAX_UPLOAD_MB = 10;

export const isAdmin = () => {
  try {
    return JSON.parse(localStorage.getItem("user"))?.role === "admin";
  } catch {
    return false;
  }
};

export const homePath = () => (isAdmin() ? "/admin" : "/home");
