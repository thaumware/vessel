import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Textarea } from "./ui/textarea";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Package, Eye, AlertTriangle, CheckCircle, Edit3, Sparkles } from "lucide-react";

const inventoryData = [
  { id: "PRD001", name: "Laptop Dell XPS 13", category: "Electrónicos", stock: 15, price: 1299.99, status: "En stock" },
  { id: "PRD002", name: "Silla de oficina ergonómica", category: "Mobiliario", stock: 8, price: 299.99, status: "Bajo stock" },
  { id: "PRD003", name: "Monitor 27'' 4K", category: "Electrónicos", stock: 23, price: 449.99, status: "En stock" },
  { id: "PRD004", name: "Teclado mecánico RGB", category: "Accesorios", stock: 0, price: 159.99, status: "Agotado" },
  { id: "PRD005", name: "Mouse inalámbrico", category: "Accesorios", stock: 34, price: 79.99, status: "En stock" },
  { id: "PRD006", name: "Impresora láser", category: "Equipos", stock: 5, price: 399.99, status: "Bajo stock" },
];

const getStatusBadge = (status: string) => {
  const variants = {
    "En stock": "default",
    "Bajo stock": "secondary",
    "Agotado": "destructive"
  } as const;
  
  return <Badge variant={variants[status as keyof typeof variants] || "default"}>{status}</Badge>;
};

export function InventorySection() {
  return (
    <section className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          {/* Left side - Enhanced Inventory Table */}
          <div>
            <Card className="overflow-hidden border-0 shadow-2xl">
              <CardHeader className="bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
                <CardTitle className="flex items-center gap-3 text-xl">
                  <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <Package className="w-6 h-6" />
                  </div>
                  Control de Inventario en Tiempo Real
                </CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-gray-50 border-b-2">
                        <TableHead className="font-semibold">ID</TableHead>
                        <TableHead className="font-semibold">Producto</TableHead>
                        <TableHead className="font-semibold">Stock</TableHead>
                        <TableHead className="font-semibold">Precio</TableHead>
                        <TableHead className="font-semibold">Estado</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {inventoryData.map((item) => (
                        <TableRow key={item.id} className="hover:bg-gray-50 transition-colors">
                          <TableCell className="font-mono text-sm font-medium">{item.id}</TableCell>
                          <TableCell>
                            <div>
                              <div className="font-medium text-gray-900">{item.name}</div>
                              <div className="text-sm text-gray-500">{item.category}</div>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <span className={`font-semibold ${item.stock === 0 ? 'text-red-600' : item.stock < 10 ? 'text-yellow-600' : 'text-green-600'}`}>
                                {item.stock}
                              </span>
                              {item.stock === 0 && <AlertTriangle className="w-4 h-4 text-red-500" />}
                              {item.stock > 0 && item.stock < 10 && <AlertTriangle className="w-4 h-4 text-yellow-500" />}
                              {item.stock >= 10 && <CheckCircle className="w-4 h-4 text-green-500" />}
                            </div>
                          </TableCell>
                          <TableCell className="font-semibold">${item.price.toFixed(2)}</TableCell>
                          <TableCell>{getStatusBadge(item.status)}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
                <div className="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t">
                  <Button className="w-full bg-emerald-600 hover:bg-emerald-700 text-white">
                    <Eye className="w-4 h-4 mr-2" />
                    Ver Inventario Completo
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right side - Enhanced Writing Area */}
          <div className="relative">
            {/* Glow effect background */}
            <div className="absolute -inset-4 bg-gradient-to-r from-purple-400 via-pink-500 to-red-500 rounded-2xl opacity-10 blur-xl"></div>
            
            <Card className="relative bg-gradient-to-br from-purple-600 via-pink-600 to-red-600 text-white border-0 shadow-2xl overflow-hidden">
              <div className="absolute inset-0 bg-black/10"></div>
              <CardHeader className="relative">
                <CardTitle className="flex items-center gap-3 text-xl">
                  <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <Edit3 className="w-6 h-6" />
                  </div>
                  ¡Toma notas inteligentes y organiza tu inventario!
                </CardTitle>
              </CardHeader>
              <CardContent className="relative space-y-4">
                <p className="text-purple-100">
                  Mantén un registro detallado de tus observaciones, planes de reposición y estrategias de inventario.
                </p>
                
                <Textarea 
                  placeholder="💡 Escribe tus ideas brillantes aquí... Observaciones sobre tendencias, planes de expansión, notas sobre proveedores, estrategias de stock..."
                  className="min-h-[300px] resize-none bg-white/20 border-white/30 text-white placeholder:text-purple-200 backdrop-blur-sm"
                />
                
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Sincronización automática</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Backup en la nube</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Colaboración en equipo</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Historial completo</span>
                  </div>
                </div>
                
                <Button className="w-full bg-white/20 hover:bg-white/30 text-white border-white/30 backdrop-blur-sm">
                  <Sparkles className="w-4 h-4 mr-2" />
                  Guardar y Sincronizar Notas
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
}