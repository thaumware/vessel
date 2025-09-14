import { Package, ArrowRight, TrendingUp, DollarSign } from "lucide-react";

export function FlowDiagram() {
  return (
    <div className="flex items-center justify-center space-x-12 max-w-4xl mx-auto">
      {/* Package/Inventory */}
      <div className="flex flex-col items-center">
        <div className="w-20 h-20 bg-amber-100 rounded-lg flex items-center justify-center mb-4">
          <Package className="w-12 h-12 text-amber-700" />
        </div>
      </div>

      {/* Arrow */}
      <div className="flex items-center">
        <ArrowRight className="w-8 h-8 text-gray-400" />
      </div>

      {/* Central Logo with Analytics */}
      <div className="flex flex-col items-center relative">
        <div className="w-32 h-32 bg-gradient-to-br from-cyan-400 via-blue-500 to-blue-700 rounded-lg flex items-center justify-center relative overflow-hidden">
          {/* Background pattern */}
          <div className="absolute inset-0 opacity-20">
            <div className="flex items-end justify-center h-full space-x-1 px-4 pb-4">
              <div className="w-2 bg-green-400 h-8"></div>
              <div className="w-2 bg-green-400 h-12"></div>
              <div className="w-2 bg-green-400 h-6"></div>
              <div className="w-2 bg-green-400 h-16"></div>
              <div className="w-2 bg-green-400 h-10"></div>
              <div className="w-2 bg-green-400 h-14"></div>
              <div className="w-2 bg-green-400 h-8"></div>
              <div className="w-2 bg-green-400 h-18"></div>
            </div>
          </div>
          
          {/* V Logo */}
          <div className="relative z-10">
            <span className="text-white font-bold text-4xl">V</span>
          </div>
          
          {/* Growth arrow */}
          <div className="absolute top-2 right-2">
            <TrendingUp className="w-6 h-6 text-green-300" />
          </div>
        </div>
      </div>

      {/* Arrow */}
      <div className="flex items-center">
        <ArrowRight className="w-8 h-8 text-gray-400" />
      </div>

      {/* Money/Revenue */}
      <div className="flex flex-col items-center">
        <div className="w-20 h-20 bg-green-100 rounded-lg flex items-center justify-center mb-4 relative">
          <div className="absolute -top-1 -left-1 w-16 h-12 bg-green-500 rounded transform rotate-12 opacity-80"></div>
          <div className="absolute top-1 left-1 w-16 h-12 bg-green-600 rounded transform -rotate-6"></div>
          <DollarSign className="w-8 h-8 text-green-800 relative z-10" />
        </div>
      </div>
    </div>
  );
}