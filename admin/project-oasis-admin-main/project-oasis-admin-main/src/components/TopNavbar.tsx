import { Bell, UserCircle, ChevronDown } from "lucide-react";
import { useAuth } from "@/contexts/AuthContext";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useNavigate } from "react-router-dom";

export function TopNavbar() {
  const { logout, adminName } = useAuth();
  const navigate = useNavigate();

  return (
    <header className="h-16 border-b border-border bg-card flex items-center justify-between px-6 shrink-0">
      {/* Left - Page context / breadcrumb area */}
      <div />

      {/* Right - Admin */}
      <div className="flex items-center gap-3">
        {/* Notifications */}
        <button className="h-9 w-9 rounded-lg flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors relative">
          <Bell className="h-5 w-5" />
          <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-destructive" />
        </button>

        {/* Admin Dropdown */}
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <button className="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-muted transition-colors">
              <div className="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                <UserCircle className="h-5 w-5 text-primary-foreground" />
              </div>
              <div className="text-left hidden sm:block">
                <p className="text-sm font-medium text-foreground leading-tight">{adminName}</p>
                <p className="text-xs text-muted-foreground leading-tight">Administrator</p>
              </div>
              <ChevronDown className="h-4 w-4 text-muted-foreground hidden sm:block" />
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-48">
            <DropdownMenuItem onClick={() => navigate("/settings")}>
              Settings
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={logout} className="text-destructive focus:text-destructive">
              Logout
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </header>
  );
}
