/**
 * API Client with comprehensive error handling
 */

export interface ApiErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public data?: ApiErrorResponse
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

/**
 * Fetch with error handling and automatic token injection
 */
export async function apiFetch<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || '/api';
  const url = `${baseUrl}${endpoint}`;

  try {
    // Inject authorization token if available
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
    };

    // Merge with existing headers
    if (options.headers) {
      if (typeof options.headers === 'object' && !Array.isArray(options.headers)) {
        Object.assign(headers, options.headers);
      }
    }

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, {
      ...options,
      headers,
    });

    // Handle non-JSON responses
    const contentType = response.headers.get('content-type');
    let data: any;

    if (contentType?.includes('application/json')) {
      data = await response.json();
    } else {
      data = await response.text();
    }

    // Handle error responses
    if (!response.ok) {
      const errorMessage =
        typeof data === 'object' && data?.message
          ? data.message
          : `API Error: ${response.status} ${response.statusText}`;

      throw new ApiError(response.status, errorMessage, data);
    }

    return data as T;
  } catch (error) {
    // Re-throw API errors as-is
    if (error instanceof ApiError) {
      throw error;
    }

    // Handle network errors
    if (error instanceof TypeError) {
      throw new ApiError(0, 'Network error. Please check your connection.', {
        message: 'Network error',
      });
    }

    // Handle unknown errors
    throw new ApiError(500, 'An unexpected error occurred', {
      message: error instanceof Error ? error.message : 'Unknown error',
    });
  }
}

/**
 * Login helper
 */
export async function login(email: string, password: string, domain: string) {
  try {
    const response = await apiFetch('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, domain }),
    });

    if ('access_token' in response) {
      localStorage.setItem('auth_token', response.access_token);
      localStorage.setItem('user', JSON.stringify(response.user));
    }

    return response;
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }
    throw new ApiError(500, 'Login failed', { message: 'Unknown error' });
  }
}

/**
 * Logout helper
 */
export async function logout() {
  try {
    await apiFetch('/auth/logout', {
      method: 'POST',
    });
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  }
}

/**
 * Get current user
 */
export async function getCurrentUser() {
  try {
    return await apiFetch('/auth/me');
  } catch (error) {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    throw error;
  }
}

/**
 * Check API health
 */
export async function checkHealth() {
  try {
    return await apiFetch('/health');
  } catch (error) {
    if (error instanceof ApiError) {
      return { status: 'error', message: error.message };
    }
    return { status: 'error', message: 'Unknown error' };
  }
}
