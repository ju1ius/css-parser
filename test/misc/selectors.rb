require 'nokogiri'

def print_xpath asts
  asts.each do |ast|
    puts ast.to_xpath
  end
end

parser = Nokogiri::CSS::Parser.new

print_xpath parser.parse('ul > li:nth-child(odd)')
print_xpath parser.parse('ul li:only-child')
