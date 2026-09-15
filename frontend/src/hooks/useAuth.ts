import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

import { api, apiError, tokenStore } from '../lib/api'
import type { User } from '../lib/types'

export function useAuth() {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  const me = useQuery({
    queryKey: ['me'],
    queryFn: async () => (tokenStore.get() ? (await api.get<{ user: User }>('/auth/me')).data.user : null),
    retry: false,
  })

  const login = useMutation({
    mutationFn: async (payload: { email: string; password: string }) =>
      (await api.post<{ token: string; user: User }>('/auth/login', payload)).data,
    onSuccess: (data) => {
      tokenStore.set(data.token)
      queryClient.setQueryData(['me'], data.user)
      toast.success(`Selamat datang, ${data.user.name}!`)
      navigate('/', { replace: true })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const register = useMutation({
    mutationFn: async (payload: { name: string; email: string; password: string }) =>
      (
        await api.post<{ token: string; user: User }>('/auth/register', {
          ...payload,
          password_confirmation: payload.password,
        })
      ).data,
    onSuccess: (data) => {
      tokenStore.set(data.token)
      queryClient.setQueryData(['me'], data.user)
      toast.success('Akun berhasil dibuat')
      navigate('/', { replace: true })
    },
    onError: (err) => toast.error(apiError(err)),
  })

  const logout = useMutation({
    mutationFn: async () => {
      if (tokenStore.get()) await api.post('/auth/logout')
    },
    onSuccess: () => {
      tokenStore.set(null)
      queryClient.clear()
      toast.success('Berhasil keluar')
      navigate('/login', { replace: true })
    },
    onError: () => {
      tokenStore.set(null)
      queryClient.clear()
      navigate('/login', { replace: true })
    },
  })

  const user = me.data ?? null
  return { user, loading: me.isLoading, isAdmin: user?.role === 'admin', login, register, logout }
}