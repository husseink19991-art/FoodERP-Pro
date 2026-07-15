import { useState, useCallback } from 'react';
import { ApiError, apiFetch } from '@/lib/api/client';

interface UseApiState<T> {
  data: T | null;
  loading: boolean;
  error: ApiError | null;
}

/**
 * Hook for making API calls with loading and error states
 */
export function useApi<T = any>(
  initialData: T | null = null
): UseApiState<T> & {
  execute: (
    endpoint: string,
    options?: RequestInit
  ) => Promise<T | null>;
} {
  const [state, setState] = useState<UseApiState<T>>({
    data: initialData,
    loading: false,
    error: null,
  });

  const execute = useCallback(
    async (endpoint: string, options?: RequestInit): Promise<T | null> => {
      setState({ data: null, loading: true, error: null });

      try {
        const data = await apiFetch<T>(endpoint, options);
        setState({ data, loading: false, error: null });
        return data;
      } catch (error) {
        const apiError =
          error instanceof ApiError
            ? error
            : new ApiError(500, 'An unexpected error occurred');

        setState({ data: null, loading: false, error: apiError });
        throw apiError;
      }
    },
    []
  );

  return {
    ...state,
    execute,
  };
}

/**
 * Hook for form submission with API calls
 */
export function useApiMutation<T = any>(
  endpoint: string,
  method: 'POST' | 'PUT' | 'DELETE' | 'PATCH' = 'POST'
): UseApiState<T> & {
  mutate: (payload: any) => Promise<T | null>;
} {
  const [state, setState] = useState<UseApiState<T>>({
    data: null,
    loading: false,
    error: null,
  });

  const mutate = useCallback(
    async (payload: any): Promise<T | null> => {
      setState({ data: null, loading: true, error: null });

      try {
        const data = await apiFetch<T>(endpoint, {
          method,
          body: JSON.stringify(payload),
        });
        setState({ data, loading: false, error: null });
        return data;
      } catch (error) {
        const apiError =
          error instanceof ApiError
            ? error
            : new ApiError(500, 'An unexpected error occurred');

        setState({ data: null, loading: false, error: apiError });
        throw apiError;
      }
    },
    [endpoint, method]
  );

  return {
    ...state,
    mutate,
  };
}
