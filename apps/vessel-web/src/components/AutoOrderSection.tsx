import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Badge } from "./ui/badge";
import { Zap, Truck, Clock, CheckCircle } from "lucide-react";

const providerOrders = [
  { 
    id: "ORD001", 
    product: "Teclado mecánico RGB", 
    supplier: "TechSupply Corp", 
    quantity: 50, 
    estimatedCost: 7999.50,
    status: "Pendiente",
    deliveryTime: "3-5 días"
  },
  { 
    id: "ORD002", 
    product: "Silla de oficina ergonómica", 
    supplier: "OfficeMax Solutions", 
    quantity: 12, 
    estimatedCost: 3599.88,
    status: "Procesando",
    deliveryTime: "7-10 días"
  },
  { 
    id: "ORD003", 
    product: "Impresora láser", 
    supplier: "PrintPro Distributors", 
    quantity: 8, 
    estimatedCost: 3199.92,
    status: "Confirmado",
    deliveryTime: "5-7 días"
  }
];

const getStatusBadge = (status: string) => {
  const colors = {
    "Pendiente": "bg-yellow-100 text-yellow-800",
    "Procesando": "bg-blue-100 text-blue-800",
    "Confirmado": "bg-green-100 text-green-800"
  };
  
  return (
    <Badge className={colors[status as keyof typeof colors] || "bg-gray-100 text-gray-800"}>
      {status}
    </Badge>
  );
};

export function AutoOrderSection() {
  return (
    <section className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Left side - Auto Purchase Orders */}
          <div>
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Truck className="w-5 h-5 text-blue-600" />
                  Solicitudes de Compra Automáticas
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* Quick Order Form */}
                <div className="p-4 bg-gray-50 rounded-lg">
                  <h4 className="font-medium mb-3">Nueva Solicitud Rápida</h4>
                  <div className="grid grid-cols-2 gap-3">
                    <Select>
                      <SelectTrigger>
                        <SelectValue placeholder="Producto" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="laptop">Laptop Dell XPS 13</SelectItem>
                        <SelectItem value="monitor">Monitor 27'' 4K</SelectItem>
                        <SelectItem value="mouse">Mouse inalámbrico</SelectItem>
                      </SelectContent>
                    </Select>
                    <Input placeholder="Cantidad" type="number" />
                    <Select>
                      <SelectTrigger>
                        <SelectValue placeholder="Proveedor" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="techsupply">TechSupply Corp</SelectItem>
                        <SelectItem value="officemax">OfficeMax Solutions</SelectItem>
                        <SelectItem value="printpro">PrintPro Distributors</SelectItem>
                      </SelectContent>
                    </Select>
                    <Button className="bg-blue-600 hover:bg-blue-700">
                      <Zap className="w-4 h-4 mr-2" />
                      Solicitar
                    </Button>
                  </div>
                </div>

                {/* Recent Orders */}
                <div className="space-y-3">
                  <h4 className="font-medium">Órdenes Recientes</h4>
                  {providerOrders.map((order) => (
                    <div key={order.id} className="border rounded-lg p-3 space-y-2">
                      <div className="flex justify-between items-start">
                        <div>
                          <p className="font-medium">{order.product}</p>
                          <p className="text-sm text-gray-600">{order.supplier}</p>
                        </div>
                        {getStatusBadge(order.status)}
                      </div>
                      <div className="flex justify-between text-sm text-gray-600">
                        <span>Cantidad: {order.quantity}</span>
                        <span>${order.estimatedCost.toLocaleString()}</span>
                      </div>
                      <div className="flex items-center gap-1 text-sm text-gray-500">
                        <Clock className="w-3 h-3" />
                        <span>Entrega: {order.deliveryTime}</span>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right side - Call to Action */}
          <div className="text-center lg:text-left">
            <div className="relative">
              {/* Lightning effect background */}
              <div className="absolute -inset-4 bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-2xl opacity-10 blur-xl"></div>
              
              <div className="relative bg-gradient-to-r from-blue-600 to-purple-700 text-white rounded-2xl p-8">
                <div className="flex items-center justify-center mb-6">
                  <div className="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <Zap className="w-8 h-8 text-yellow-900" />
                  </div>
                </div>
                
                <h2 className="text-3xl lg:text-4xl font-bold mb-4 leading-tight">
                  ¡Haz tus pedidos 
                  <span className="block text-yellow-300">automáticamente</span>
                  cuando te quedes sin stock!
                </h2>
                
                <p className="text-lg text-blue-100 mb-6">
                  Nunca más te quedes sin inventario. Nuestro sistema inteligente monitorea tu stock 24/7 y realiza pedidos automáticos a tus proveedores favoritos.
                </p>
                
                <div className="grid grid-cols-2 gap-4 mb-6 text-sm">
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Monitoreo 24/7</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Pedidos inteligentes</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Múltiples proveedores</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Ahorro de tiempo</span>
                  </div>
                </div>
                
                <Button size="lg" className="bg-yellow-400 hover:bg-yellow-300 text-yellow-900 font-bold">
                  <Zap className="w-5 h-5 mr-2" />
                  Activar Automatización
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}