import { useState } from "react";
import { Plus, Search, Pencil, Trash2, MapPin, Calendar, DollarSign, User, Filter, FolderKanban } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { StatusBadge } from "@/components/StatusBadge";
import { ProjectFormDialog } from "@/components/ProjectFormDialog";
import { DeleteConfirmDialog } from "@/components/DeleteConfirmDialog";
import { initialProjects, type Project, type ProjectStatus } from "@/lib/projectsData";
import { motion, AnimatePresence } from "framer-motion";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "sonner";

const statuses: ProjectStatus[] = ["Completed", "In Progress", "Planning", "On Hold"];

export default function ProjectsPage() {
  const [projects, setProjects] = useState<Project[]>(initialProjects);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [formOpen, setFormOpen] = useState(false);
  const [editingProject, setEditingProject] = useState<Project | null>(null);
  const [deleteProject, setDeleteProject] = useState<Project | null>(null);

  const filtered = projects.filter((p) => {
    const matchesSearch = p.name.toLowerCase().includes(search.toLowerCase()) || p.client.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === "all" || p.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const handleSave = (data: Omit<Project, "id"> & { id?: string }) => {
    if (data.id) {
      setProjects((prev) => prev.map((p) => (p.id === data.id ? { ...p, ...data } as Project : p)));
      toast.success("Project updated successfully");
    } else {
      const newProject: Project = { ...data, id: Date.now().toString() } as Project;
      setProjects((prev) => [...prev, newProject]);
      toast.success("Project created successfully");
    }
  };

  const handleDelete = () => {
    if (deleteProject) {
      setProjects((prev) => prev.filter((p) => p.id !== deleteProject.id));
      toast.success("Project deleted successfully");
      setDeleteProject(null);
    }
  };

  const handleStatusChange = (projectId: string, newStatus: ProjectStatus) => {
    setProjects((prev) =>
      prev.map((p) => (p.id === projectId ? { ...p, status: newStatus } : p))
    );
    toast.success("Status updated");
  };

  const statusCounts = {
    all: projects.length,
    ...Object.fromEntries(statuses.map((s) => [s, projects.filter((p) => p.status === s).length])),
  };

  return (
    <div className="p-6 lg:p-8 max-w-7xl">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl lg:text-3xl font-heading font-bold text-foreground">Projects</h1>
          <p className="text-muted-foreground mt-1">Manage and track all company projects</p>
        </div>
        <Button onClick={() => { setEditingProject(null); setFormOpen(true); }} className="gap-2">
          <Plus className="h-4 w-4" />
          New Project
        </Button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
        {[
          { label: "Total", count: statusCounts.all, color: "bg-primary/10 text-primary" },
          { label: "Completed", count: statusCounts["Completed"], color: "bg-status-completed/10 text-status-completed" },
          { label: "In Progress", count: statusCounts["In Progress"], color: "bg-status-in-progress/10 text-status-in-progress" },
          { label: "Planning", count: statusCounts["Planning"], color: "bg-status-planning/10 text-status-planning" },
          { label: "On Hold", count: statusCounts["On Hold"], color: "bg-status-on-hold/10 text-status-on-hold" },
        ].map((stat) => (
          <div key={stat.label} className="bg-card rounded-xl p-4 border border-border">
            <p className="text-sm text-muted-foreground">{stat.label}</p>
            <p className={`text-2xl font-heading font-bold mt-1 ${stat.color.split(" ")[1]}`}>{stat.count}</p>
          </div>
        ))}
      </div>

      {/* Filters */}
      <div className="flex flex-col sm:flex-row gap-3 mb-6">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search projects or clients..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9"
          />
        </div>
        <Select value={statusFilter} onValueChange={setStatusFilter}>
          <SelectTrigger className="w-full sm:w-44">
            <Filter className="h-4 w-4 mr-2 text-muted-foreground" />
            <SelectValue placeholder="All Statuses" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            {statuses.map((s) => (
              <SelectItem key={s} value={s}>{s}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Project Cards */}
      <AnimatePresence mode="popLayout">
        <div className="grid gap-4">
          {filtered.map((project) => (
            <motion.div
              key={project.id}
              layout
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="bg-card border border-border rounded-xl p-5 hover:shadow-md transition-shadow"
            >
              <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-3 mb-2">
                    <h3 className="font-heading font-semibold text-lg text-foreground truncate">{project.name}</h3>
                    <StatusBadge status={project.status} />
                  </div>
                  <p className="text-sm text-muted-foreground mb-3 line-clamp-2">{project.description}</p>
                  <div className="flex flex-wrap gap-x-5 gap-y-2 text-sm text-muted-foreground">
                    <span className="flex items-center gap-1.5"><MapPin className="h-3.5 w-3.5" />{project.location}</span>
                    <span className="flex items-center gap-1.5"><Calendar className="h-3.5 w-3.5" />{project.startDate}</span>
                    <span className="flex items-center gap-1.5"><DollarSign className="h-3.5 w-3.5" />{project.budget}</span>
                    <span className="flex items-center gap-1.5"><User className="h-3.5 w-3.5" />{project.client}</span>
                  </div>
                </div>
                <div className="flex items-center gap-2 shrink-0">
                  {/* Inline Status Update */}
                  <Select
                    value={project.status}
                    onValueChange={(v) => handleStatusChange(project.id, v as ProjectStatus)}
                  >
                    <SelectTrigger className="w-36 h-9 text-xs">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {statuses.map((s) => (
                        <SelectItem key={s} value={s}>{s}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => { setEditingProject(project); setFormOpen(true); }}
                    className="gap-1.5"
                  >
                    <Pencil className="h-3.5 w-3.5" />
                    Edit
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setDeleteProject(project)}
                    className="gap-1.5 text-destructive hover:text-destructive"
                  >
                    <Trash2 className="h-3.5 w-3.5" />
                    Delete
                  </Button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </AnimatePresence>

      {filtered.length === 0 && (
        <div className="text-center py-16 text-muted-foreground">
          <FolderKanban className="h-12 w-12 mx-auto mb-3 opacity-40" />
          <p className="text-lg">No projects found</p>
          <p className="text-sm mt-1">Try adjusting your search or filters</p>
        </div>
      )}

      <ProjectFormDialog
        open={formOpen}
        onOpenChange={setFormOpen}
        project={editingProject}
        onSave={handleSave}
      />

      <DeleteConfirmDialog
        open={!!deleteProject}
        onOpenChange={(open) => !open && setDeleteProject(null)}
        projectName={deleteProject?.name || ""}
        onConfirm={handleDelete}
      />
    </div>
  );
}
