export const API = "https://itconect.free.je/api";

export const getMediaUrl = (path) => {
  if (!path) return null;
  if (/^https?:\/\//.test(path)) return path;
  const cleanPath = path.startsWith("/") ? path.slice(1) : path;
  return `${API}/${cleanPath}`;
};

const ICONS = { friend_request: "👥", friend_accept: "🤝", message: "💬" };
export const getNotificationIcon = (type) => ICONS[type] || "🔔";

// Dung lượng tối đa của tệp đính kèm khi đăng bài (MB) - InfinityFree chỉ cho phép tối đa 10MB/file
export const MAX_UPLOAD_MB = 10;

export const isAdmin = () => {
  try {
    return String(JSON.parse(localStorage.getItem("user"))?.role).trim().toLowerCase() === "admin";
  } catch {
    return false;
  }
};

export const homePath = () => (isAdmin() ? "/admin" : "/home");

// Gửi POST JSON; ném lỗi có thông báo rõ ràng để hiển thị cho người dùng
export const postJson = async (url, body) => {
  let response;

  try {
    response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
  } catch {
    throw new Error("Không kết nối được tới server!");
  }

  let data;

  try {
    data = await response.json();
  } catch {
    throw new Error(`Server trả về lỗi ${response.status}. File PHP tương ứng có thể chưa được upload lên server.`);
  }

  if (!response.ok) {
    throw new Error(data.message || "Có lỗi xảy ra!");
  }

  return data;
};
