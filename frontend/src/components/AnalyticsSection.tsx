import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Textarea } from "./ui/textarea";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";
import { Progress } from "./ui/progress";
import { Button } from "./ui/button";
import { TrendingUp, TrendingDown, Minus, BarChart3, Target, CheckCircle, Brain, Rocket } from "lucide-react";

const salesData = [
  { month: "Enero", ventas: 45000, crecimiento: 12, trend: "up" },
  { month: "Febrero", ventas: 52000, crecimiento: 15.5, trend: "up" },
  { month: "Marzo", ventas: 48000, crecimiento: -7.7, trend: "down" },
  { month: "Abril", ventas: 61000, crecimiento: 27.1, trend: "up" },
  { month: "Mayo", ventas: 58000, crecimiento: -4.9, trend: "down" },
  { month: "Junio", ventas: 65000, crecimiento: 12.1, trend: "up" },
];

const topProducts = [
  { product: "Laptop Dell XPS 13", sold: 89, revenue: 115689 },
  { product: "Monitor 27'' 4K", sold: 76, revenue: 34199 },
  { product: "Silla ergonómica", sold: 45, revenue: 13499 },
  { product: "Teclado mecánico", sold: 134, revenue: 21439 },
];

const getTrendIcon = (trend: string) => {
  if (trend === "up") return <TrendingUp className="w-4 h-4 text-green-600" />;
  if (trend === "down") return <TrendingDown className="w-4 h-4 text-red-600" />;
  return <Minus className="w-4 h-4 text-gray-400" />;
};

const getTrendColor = (trend: string) => {
  if (trend === "up") return "text-green-600";
  if (trend === "down") return "text-red-600";
  return "text-gray-600";
};

export function AnalyticsSection() {
  return (
    <section className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          {/* Left side - Enhanced Writing Area */}
          <div className="relative">
            {/* Glow effect background */}
            <div className="absolute -inset-4 bg-gradient-to-r from-blue-400 via-indigo-500 to-purple-600 rounded-2xl opacity-10 blur-xl"></div>
            
            <Card className="relative bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-600 text-white border-0 shadow-2xl overflow-hidden">
              <div className="absolute inset-0 bg-black/10"></div>
              <CardHeader className="relative">
                <CardTitle className="flex items-center gap-3 text-xl">
                  <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <Brain className="w-6 h-6" />
                  </div>
                  ¡Convierte datos en decisiones inteligentes!
                </CardTitle>
              </CardHeader>
              <CardContent className="relative space-y-4">
                <p className="text-blue-100">
                  Analiza tendencias, identifica oportunidades y desarrolla estrategias ganadoras basadas en datos reales.
                </p>
                
                <Textarea 
                  placeholder="🚀 Escribe tus insights aquí... Análisis de mercado, estrategias de crecimiento, conclusiones sobre tendencias, planes de acción..."
                  className="min-h-[350px] resize-none bg-white/20 border-white/30 text-white placeholder:text-blue-200 backdrop-blur-sm"
                />
                
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Análisis predictivo</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Reportes automáticos</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Alertas inteligentes</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-300" />
                    <span>Exportación fácil</span>
                  </div>
                </div>
                
                <Button className="w-full bg-white/20 hover:bg-white/30 text-white border-white/30 backdrop-blur-sm">
                  <Rocket className="w-4 h-4 mr-2" />
                  Generar Reporte Ejecutivo
                </Button>
              </CardContent>
            </Card>
          </div>

          {/* Right side - Enhanced Dynamic Statistics */}
          <div className="space-y-6">
            {/* Sales Performance Table */}
            <Card className="overflow-hidden border-0 shadow-2xl">
              <CardHeader className="bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                <CardTitle className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <BarChart3 className="w-5 h-5" />
                  </div>
                  Rendimiento de Ventas Mensual
                </CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                <Table>
                  <TableHeader>
                    <TableRow className="bg-gray-50">
                      <TableHead className="font-semibold">Mes</TableHead>
                      <TableHead className="font-semibold">Ventas</TableHead>
                      <TableHead className="font-semibold">Crecimiento</TableHead>
                      <TableHead className="font-semibold">Tendencia</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {salesData.map((item) => (
                      <TableRow key={item.month} className="hover:bg-gray-50 transition-colors">
                        <TableCell className="font-medium">{item.month}</TableCell>
                        <TableCell className="font-semibold">${item.ventas.toLocaleString()}</TableCell>
                        <TableCell className={`font-semibold ${getTrendColor(item.trend)}`}>
                          {item.crecimiento > 0 ? '+' : ''}{item.crecimiento}%
                        </TableCell>
                        <TableCell>{getTrendIcon(item.trend)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
                <div className="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t">
                  <div className="text-center text-sm text-gray-600">
                    📈 Crecimiento promedio: <span className="font-semibold text-green-600">+8.3%</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Top Products Performance */}
            <Card className="overflow-hidden border-0 shadow-2xl">
              <CardHeader className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                <CardTitle className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <Target className="w-5 h-5" />
                  </div>
                  Productos Estrella
                </CardTitle>
              </CardHeader>
              <CardContent className="p-6">
                <div className="space-y-4">
                  {topProducts.map((item, index) => (
                    <div key={item.product} className="relative">
                      <div className="flex justify-between items-center mb-2">
                        <div className="flex items-center gap-3">
                          <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm ${
                            index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-500' : 'bg-blue-500'
                          }`}>
                            #{index + 1}
                          </div>
                          <span className="font-medium">{item.product}</span>
                        </div>
                        <span className="text-sm font-semibold text-gray-600">{item.sold} unidades</span>
                      </div>
                      <div className="flex justify-between items-center text-sm mb-2">
                        <span className="text-gray-600">Ingresos: <span className="font-semibold">${item.revenue.toLocaleString()}</span></span>
                        <span className="font-semibold text-blue-600">{Math.round((item.sold / 150) * 100)}%</span>
                      </div>
                      <Progress 
                        value={(item.sold / 150) * 100} 
                        className="h-3"
                      />
                    </div>
                  ))}
                </div>
                <Button className="w-full mt-4 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white border-0">
                  <Target className="w-4 h-4 mr-2" />
                  Ver Análisis Detallado
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
}