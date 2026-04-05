import { FolderKanban, CheckCircle, Clock, Compass, PauseCircle } from "lucide-react";

const stats = [
  { label: "Total Projects", value: "5", icon: FolderKanban, color: "text-primary" },
  { label: "Completed", value: "1", icon: CheckCircle, color: "text-status-completed" },
  { label: "In Progress", value: "2", icon: Clock, color: "text-status-in-progress" },
  { label: "Planning", value: "1", icon: Compass, color: "text-status-planning" },
  { label: "On Hold", value: "1", icon: PauseCircle, color: "text-status-on-hold" },
];

export default function DashboardPage() {
  return (
    <div className="p-6 lg:p-8 max-w-7xl">
      <div className="mb-8">
        <h1 className="text-2xl lg:text-3xl font-heading font-bold text-foreground">Dashboard</h1>
        <p className="text-muted-foreground mt-1">Welcome back to Diar360 Admin</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {stats.map((stat) => (
          <div key={stat.label} className="bg-card border border-border rounded-xl p-5 flex flex-col gap-3">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground font-medium">{stat.label}</span>
              <stat.icon className={`h-5 w-5 ${stat.color}`} />
            </div>
            <p className={`text-3xl font-heading font-bold ${stat.color}`}>{stat.value}</p>
          </div>
        ))}
      </div>

      <div className="mt-8 bg-card border border-border rounded-xl p-6">
        <h2 className="font-heading text-lg font-semibold mb-4 text-foreground">Quick Actions</h2>
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <a href="/projects" className="flex items-center gap-3 p-4 rounded-lg bg-primary/5 hover:bg-primary/10 transition-colors border border-primary/10">
            <FolderKanban className="h-5 w-5 text-primary" />
            <span className="text-sm font-medium text-foreground">Manage Projects</span>
          </a>
        </div>
      </div>
    </div>
  );
}
