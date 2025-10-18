/**
 * Vessel Integration SDK
 * 
 * SDK para integrar el sistema Vessel en aplicaciones externas.
 * Soporta modo standalone e integrado.
 */

export interface VesselConfig {
  /**
   * Modo de operación
   */
  mode: 'standalone' | 'integrated';
  
  /**
   * URL base del API
   */
  baseUrl: string;
  
  /**
   * Configuración de autenticación
   */
  auth?: {
    type: 'internal' | 'external';
    // Autenticación interna (standalone)
    credentials?: {
      username: string;
      password: string;
    };
    // Autenticación externa (integrated)
    tokenProvider?: () => Promise<string>;
    authServiceUrl?: string;
  };
  
  /**
   * Configuración de IoT
   */
  iot?: {
    enabled: boolean;
    // Mock devices para desarrollo (standalone)
    mockDevices?: boolean;
    // Servicio IoT externo (integrated)
    iotServiceUrl?: string;
  };
}

export class VesselClient {
  constructor(private config: VesselConfig) {}
  
  /**
   * Obtiene el token de autenticación
   */
  private async getAuthToken(): Promise<string> {
    if (this.config.auth?.type === 'internal') {
      // TODO: Implementar login interno
      return 'internal-token';
    } else if (this.config.auth?.tokenProvider) {
      return await this.config.auth.tokenProvider();
    }
    throw new Error('No authentication configured');
  }
  
  /**
   * Ejemplo de método del SDK
   */
  async getInventoryItems(): Promise<any[]> {
    const token = await this.getAuthToken();
    // TODO: Implementar llamada al API
    return [];
  }
}

export default VesselClient;
