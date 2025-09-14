import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Button } from "./ui/button";
import { Smartphone, Tablet, Laptop, Monitor, CheckCircle, Zap, Globe, Wifi } from "lucide-react";

const devices = [
  {
    icon: Smartphone,
    name: "Móviles",
    description: "iOS y Android",
    features: ["Aplicación nativa", "Sincronización en tiempo real", "Notificaciones push", "Modo offline"],
    gradient: "from-pink-500 to-rose-600"
  },
  {
    icon: Tablet,
    name: "Tablets",
    description: "iPad y Android",
    features: ["Interfaz optimizada", "Gestos táctiles", "Pantalla dividida", "Modo presentación"],
    gradient: "from-purple-500 to-indigo-600"
  },
  {
    icon: Laptop,
    name: "Notebooks",
    description: "Windows, Mac, Linux",
    features: ["Aplicación de escritorio", "Atajos de teclado", "Múltiples ventanas", "Exportación avanzada"],
    gradient: "from-blue-500 to-cyan-600"
  },
  {
    icon: Monitor,
    name: "Desktop",
    description: "Todas las plataformas",
    features: ["Pantalla completa", "Múltiples monitores", "Rendimiento máximo", "Integraciones avanzadas"],
    gradient: "from-emerald-500 to-teal-600"
  }
];

export function CompatibilitySection() {
  return (
    <section className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header with Call to Action */}
        <div className="text-center mb-16 relative">
          {/* Glow effect background */}
          <div className="absolute -inset-8 bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 rounded-3xl opacity-10 blur-2xl"></div>
          
          <div className="relative bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900 text-white rounded-3xl p-12 overflow-hidden">
            <div className="absolute inset-0 bg-black/20"></div>
            
            {/* Background pattern */}
            <div className="absolute inset-0 opacity-10">
              <div className="flex items-center justify-center h-full">
                <div className="grid grid-cols-8 gap-4">
                  {Array.from({ length: 32 }).map((_, i) => (
                    <div key={i} className="w-2 h-2 bg-white rounded-full animate-pulse" style={{ animationDelay: `${i * 100}ms` }}></div>
                  ))}
                </div>
              </div>
            </div>
            
            <div className="relative z-10">
              <div className="flex items-center justify-center mb-6">
                <div className="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center">
                  <Globe className="w-10 h-10 text-white" />
                </div>
              </div>
              
              <h2 className="text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                <span className="bg-gradient-to-r from-yellow-300 via-pink-300 to-purple-300 bg-clip-text text-transparent">
                  Funciona en todos tus dispositivos
                </span>
              </h2>
              
              <p className="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
                Accede a tu inventario desde cualquier lugar, en cualquier momento. 
                Sincronización perfecta entre todos tus dispositivos con la mejor experiencia de usuario.
              </p>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div className="flex items-center gap-2 text-green-300">
                  <CheckCircle className="w-5 h-5" />
                  <span>100% Sincronizado</span>
                </div>
                <div className="flex items-center gap-2 text-green-300">
                  <CheckCircle className="w-5 h-5" />
                  <span>Ultra Rápido</span>
                </div>
                <div className="flex items-center gap-2 text-green-300">
                  <CheckCircle className="w-5 h-5" />
                  <span>Modo Offline</span>
                </div>
                <div className="flex items-center gap-2 text-green-300">
                  <CheckCircle className="w-5 h-5" />
                  <span>Seguridad Total</span>
                </div>
              </div>
              
              <Button size="lg" className="bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-300 hover:to-orange-400 text-black font-bold border-0 shadow-2xl">
                <Zap className="w-5 h-5 mr-2" />
                Probar en Todos mis Dispositivos
              </Button>
            </div>
          </div>
        </div>

        {/* Device Compatibility Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {devices.map((device, _index) => {
            const IconComponent = device.icon;
            return (
              <Card key={device.name} className="relative overflow-hidden border-0 shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-105">
                <div className={`absolute inset-0 bg-gradient-to-br ${device.gradient} opacity-5`}></div>
                
                <CardHeader className={`bg-gradient-to-r ${device.gradient} text-white relative`}>
                  <div className="absolute inset-0 bg-black/10"></div>
                  <CardTitle className="flex items-center gap-3 relative z-10">
                    <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                      <IconComponent className="w-6 h-6" />
                    </div>
                    <div>
                      <div className="font-bold">{device.name}</div>
                      <div className="text-sm opacity-90">{device.description}</div>
                    </div>
                  </CardTitle>
                </CardHeader>
                
                <CardContent className="p-6 relative bg-white">
                  <div className="space-y-3">
                    {device.features.map((feature, featureIndex) => (
                      <div key={featureIndex} className="flex items-center gap-3">
                        <div className={`w-6 h-6 bg-gradient-to-r ${device.gradient} rounded-full flex items-center justify-center`}>
                          <CheckCircle className="w-4 h-4 text-white" />
                        </div>
                        <span className="text-gray-700">{feature}</span>
                      </div>
                    ))}
                  </div>
                  
                  <div className="mt-6 pt-4 border-t border-gray-100">
                    <div className="flex items-center justify-center gap-2 text-sm text-gray-500">
                      <Wifi className="w-4 h-4" />
                      <span>Conexión segura SSL</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Bottom CTA */}
        <div className="text-center mt-16">
          <Card className="inline-block bg-gradient-to-r from-gray-900 to-gray-800 border-0 shadow-2xl">
            <CardContent className="p-8 text-white">
              <h3 className="text-2xl font-bold mb-4">¿Listo para comenzar?</h3>
              <p className="text-gray-300 mb-6">
                Descarga nuestra aplicación en tu dispositivo favorito y comienza a gestionar tu inventario de manera inteligente.
              </p>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Button variant="outline" className="bg-white/10 border-white/20 text-white hover:bg-white/20">
                  <Smartphone className="w-4 h-4 mr-2" />
                  App Store
                </Button>
                <Button variant="outline" className="bg-white/10 border-white/20 text-white hover:bg-white/20">
                  <Smartphone className="w-4 h-4 mr-2" />
                  Google Play
                </Button>
                <Button variant="outline" className="bg-white/10 border-white/20 text-white hover:bg-white/20">
                  <Laptop className="w-4 h-4 mr-2" />
                  Windows
                </Button>
                <Button variant="outline" className="bg-white/10 border-white/20 text-white hover:bg-white/20">
                  <Monitor className="w-4 h-4 mr-2" />
                  Web App
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
}