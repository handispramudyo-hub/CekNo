import { render, screen } from '@testing-library/react'

import { RiskGauge } from './RiskGauge'

describe('RiskGauge', () => {
  it('renders numeric score and label', () => {
    render(<RiskGauge score={78} level="high" />)
    expect(screen.getByRole('img', { name: /skor risiko 78 dari 100/i })).toBeInTheDocument()
    expect(screen.getByText('Risiko Tinggi')).toBeInTheDocument()
  })

  it('shows dash when score is null', () => {
    render(<RiskGauge score={null} level={null} />)
    expect(screen.getByText('–')).toBeInTheDocument()
  })
})