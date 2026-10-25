import { Navigate } from "react-router-dom";
import { isAdmin } from "./utils";

// Chặn truy cập nếu không phải là Admin
function AdminRoute({ children }) {
  return isAdmin() ? children : <Navigate to="/" replace />;
}

export default AdminRoute;

